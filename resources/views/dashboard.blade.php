@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex flex-column">
            <h5 class="card-title">1. Upload Recipients</h5>
            <p class="card-text text-muted">Upload a CSV with <code>name,email</code>.</p>
            <a href="{{ route('campaigns.create') }}" class="btn btn-primary mt-auto">Upload Recipients</a>
        </div></div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex flex-column">
            <h5 class="card-title">2. Compose Email</h5>
            <p class="card-text text-muted">Subject, body and optional attachment, on the same page as the upload.</p>
            <a href="{{ route('campaigns.create') }}" class="btn btn-primary mt-auto">Compose Email</a>
        </div></div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex flex-column">
            <h5 class="card-title">3. Preview &amp; Send</h5>
            @if ($draft)
                <p class="card-text text-muted">Draft ready: <strong>{{ $draft->subject }}</strong> ({{ $draft->total_count }} recipients).</p>
                <a href="{{ route('campaigns.show', $draft) }}" class="btn btn-success mt-auto">Review &amp; Send</a>
            @else
                <p class="card-text text-muted">No draft yet. Upload recipients first.</p>
                <button class="btn btn-success mt-auto" disabled>Send Email</button>
            @endif
        </div></div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card h-100"><div class="card-body d-flex flex-column">
            <h5 class="card-title">4. Email History</h5>
            <p class="card-text text-muted">{{ $campaignCount }} campaign(s) so far.</p>
            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary mt-auto">Email History</a>
        </div></div>
    </div>
</div>
@endsection
