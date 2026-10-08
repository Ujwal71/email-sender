@extends('layouts.app')
@section('title', 'Preview')

@section('content')
<h1 class="h3 mb-3">Email Preview</h1>

<div class="card mb-3"><div class="card-body">
    <p class="mb-1"><strong>To:</strong> {{ $recipient->name }} &lt;{{ $recipient->email }}&gt;</p>
    <p class="mb-1"><strong>Subject:</strong> {{ $subject }}</p>
    <p class="mb-0"><strong>Attachment:</strong> {{ $campaign->attachment_name ?? 'None' }}</p>
</div></div>

{{-- Sandboxed iframe: shows exactly what the recipient gets, and cannot run scripts. --}}
<iframe sandbox srcdoc="{{ $html }}" class="w-100 bg-white border rounded" style="height: 420px;"></iframe>

<div class="mt-3">
    <a href="{{ route('campaigns.show', $campaign) }}" class="btn btn-secondary">&larr; Back</a>
</div>
@endsection
