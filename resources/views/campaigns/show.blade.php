@extends('layouts.app')
@section('title', $campaign->subject)

@if ($campaign->status === 'sending')
    @push('head') <meta http-equiv="refresh" content="5"> @endpush
@endif

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <h1 class="h3 mb-0">{{ $campaign->subject }}</h1>
    <span class="badge fs-6 text-bg-{{ ['draft' => 'secondary', 'sending' => 'warning', 'completed' => 'success'][$campaign->status] }}">
        {{ ucfirst($campaign->status) }}
    </span>
</div>

@if (session('invalid_rows'))
    @include('campaigns._invalid', ['rows' => session('invalid_rows')])
@endif

{{-- ============ DRAFT ============ --}}
@if ($campaign->isDraft())
    <div class="card mb-4"><div class="card-body">
        <p class="mb-1"><strong>Recipients:</strong> {{ $campaign->total_count }}</p>
        <p class="mb-3"><strong>Attachment:</strong> {{ $campaign->attachment_name ?? 'None' }}</p>

        <a href="{{ route('campaigns.preview', $campaign) }}" class="btn btn-outline-primary">Preview Email</a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmSend">Send Email</button>
        <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}" class="d-inline"
              onsubmit="return confirm('Delete this draft?')">
            @csrf @method('DELETE')
            <button class="btn btn-link text-danger">Delete draft</button>
        </form>
    </div></div>

    <div class="modal fade" id="confirmSend" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm sending</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1"><strong>Recipients:</strong> {{ $campaign->total_count }}</p>
                <p class="mb-1"><strong>Subject:</strong> {{ $campaign->subject }}</p>
                <p class="mb-3"><strong>Attachment:</strong> {{ $campaign->attachment_name ?? 'None' }}</p>
                <p class="text-danger mb-0">This will email every recipient and cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('campaigns.send', $campaign) }}">
                    @csrf
                    <button class="btn btn-success">Yes, send now</button>
                </form>
            </div>
        </div></div>
    </div>
@endif

{{-- ============ SENDING / COMPLETED ============ --}}
@unless ($campaign->isDraft())
    @if ($campaign->status === 'sending')
        <div class="alert alert-warning">Sending in progress. This page refreshes every 5 seconds.</div>
    @endif

    <div class="row g-3 mb-4 text-center">
        <div class="col"><div class="card"><div class="card-body">
            <div class="text-muted">Total</div><div class="fs-3">{{ $campaign->total_count }}</div>
        </div></div></div>
        <div class="col"><div class="card"><div class="card-body">
            <div class="text-muted">Sent</div><div class="fs-3 text-success">{{ $campaign->sent_count }}</div>
        </div></div></div>
        <div class="col"><div class="card"><div class="card-body">
            <div class="text-muted">Failed</div><div class="fs-3 text-danger">{{ $campaign->failed_count }}</div>
        </div></div></div>
    </div>

    <p class="text-muted">
        Attachment: {{ $campaign->attachment_name ?? 'None' }}
        @if ($campaign->sent_at) &middot; Finished {{ $campaign->sent_at->format('Y-m-d H:i') }} @endif
    </p>

    @if ($failed->isNotEmpty())
        <div class="card border-danger mb-4">
            <div class="card-header text-bg-danger">Failed ({{ $failed->count() }})</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Email</th><th>Reason</th></tr></thead>
                <tbody>
                @foreach ($failed as $r)
                    <tr><td>{{ $r->email }}</td><td>{{ $r->error ?? 'Unknown error' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endunless

{{-- ============ RECIPIENTS ============ --}}
<h2 class="h5">Recipients ({{ $campaign->total_count }})</h2>
<div class="table-responsive bg-white border rounded">
    <table class="table table-sm table-hover mb-0">
        <thead>
        <tr>
            <th>#</th><th>Name</th><th>Email</th>
            @unless ($campaign->isDraft()) <th>Status</th> @endunless
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($recipients as $r)
            <tr>
                <td>{{ $loop->iteration + ($recipients->currentPage() - 1) * $recipients->perPage() }}</td>
                <td>{{ $r->name }}</td>
                <td>{{ $r->email }}</td>
                @unless ($campaign->isDraft())
                    <td>
                        <span class="badge text-bg-{{ ['pending' => 'secondary', 'sent' => 'success', 'failed' => 'danger'][$r->status] }}">{{ $r->status }}</span>
                    </td>
                @endunless
                <td class="text-end">
                    <a href="{{ route('campaigns.preview', [$campaign, 'recipient' => $r->id]) }}">Preview</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $recipients->links('pagination::bootstrap-5') }}</div>
@endsection
