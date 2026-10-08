<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignJob;
use App\Models\EmailCampaign;
use App\Services\RecipientImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CampaignController extends Controller
{
    public function index()
    {
        return view('campaigns.index', [
            'campaigns' => EmailCampaign::latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('campaigns.create', [
            'defaultBody' => "Dear {{name}},\n\n\n\nRegards,\nIT Department",
        ]);
    }
    public function store(Request $request, RecipientImporter $importer)
    {
        $data = $request->validate([
            'recipients_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:20000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,txt,csv,zip'],
        ]);

        try {
            [$valid, $invalid] = $importer->parse($request->file('recipients_file')->getRealPath());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['recipients_file' => $e->getMessage()]);
        }

        if (!$valid) {
            return back()->withInput()
                ->withErrors(['recipients_file' => 'No valid recipients were found in the file.'])
                ->with('invalid_rows', $invalid);
        }

        $attachment = $request->file('attachment');

        $campaign = DB::transaction(function () use ($data, $valid, $attachment) {
            $campaign = EmailCampaign::create([
                'subject' => $data['subject'],
                'body' => $data['body'],
                'attachment_path' => $attachment?->store('attachments'),
                'attachment_name' => $attachment ? basename($attachment->getClientOriginalName()) : null,
                'total_count' => count($valid),
            ]);
            $campaign->recipients()->createMany($valid);

            return $campaign;
        });

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', count($valid) . ' recipients imported.')
            ->with('invalid_rows', $invalid);
    }

    public function show(EmailCampaign $campaign)
    {
        if ($campaign->status === 'sending') {
            $campaign->syncCounts();
        }

        return view('campaigns.show', [
            'campaign' => $campaign,
            'recipients' => $campaign->recipients()->orderBy('id')->paginate(50),
            'failed' => $campaign->recipients()->where('status', 'failed')->limit(500)->get(),
        ]);
    }

    public function preview(Request $request, EmailCampaign $campaign)
    {
        $recipient = $campaign->recipients()->find($request->query('recipient'))
            ?? $campaign->recipients()->orderBy('id')->firstOrFail();

        [$subject, $body] = $campaign->renderFor($recipient->name, $recipient->email);

        return view('campaigns.preview', [
            'campaign' => $campaign,
            'recipient' => $recipient,
            'subject' => $subject,
            'html' => view('emails.campaign', ['htmlBody' => $body])->render(),
        ]);
    }

    public function send(EmailCampaign $campaign)
    {
        // Atomic draft -> sending switch prevents double-sends on double-click.
        $claimed = EmailCampaign::whereKey($campaign->id)->where('status', 'draft')
            ->update(['status' => 'sending']);
        abort_if($claimed === 0, 409, 'This campaign has already been sent.');

        SendCampaignJob::dispatch($campaign->fresh());

        return redirect()->route('campaigns.show', $campaign);
    }

    public function destroy(EmailCampaign $campaign)
    {
        abort_unless($campaign->isDraft(), 403, 'Only drafts can be deleted.');

        if ($campaign->attachment_path) {
            Storage::delete($campaign->attachment_path);
        }
        $campaign->delete();

        return redirect()->route('dashboard')->with('success', 'Draft deleted.');
    }
}
