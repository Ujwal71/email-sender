@extends('layouts.app')
@section('title', 'Email History')

@section('content')
<h1 class="h3 mb-3">Email History</h1>

<div class="table-responsive bg-white border rounded">
    <table class="table table-hover align-middle mb-0">
        <thead>
        <tr><th>Date / time</th><th>Subject</th><th>Recipients</th><th>Sent</th><th>Failed</th><th>Status</th></tr>
        </thead>
        <tbody>
        @forelse ($campaigns as $c)
            <tr>
                <td>{{ $c->created_at->format('Y-m-d H:i') }}</td>
                <td><a href="{{ route('campaigns.show', $c) }}">{{ $c->subject }}</a></td>
                <td>{{ $c->total_count }}</td>
                <td class="text-success">{{ $c->sent_count }}</td>
                <td class="text-danger">{{ $c->failed_count }}</td>
                <td>{{ ucfirst($c->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Nothing here yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $campaigns->links('pagination::bootstrap-5') }}</div>
@endsection
