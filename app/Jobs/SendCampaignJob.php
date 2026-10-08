<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends every pending recipient of a campaign, one email at a time.
 * With QUEUE_CONNECTION=sync it runs inline; with "database" it runs in the
 * background via `php artisan queue:work`.
 */
class SendCampaignJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 3600;

    public function __construct(public EmailCampaign $campaign)
    {
    }

    public function handle(): void
    {
        set_time_limit(0);

        // Only pending rows, so a re-run never sends duplicates.
        foreach ($this->campaign->recipients()->where('status', 'pending')->lazyById(100) as $recipient) {
            try {
                Mail::to($recipient->email, $recipient->name)->send(new CampaignMail($this->campaign, $recipient));
                $recipient->update(['status' => 'sent', 'sent_at' => now()]);
            } catch (Throwable $e) {
                $recipient->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 500)]);
            }
        }

        $this->campaign->syncCounts();
        $this->campaign->update(['status' => 'completed', 'sent_at' => now()]);
    }
}
