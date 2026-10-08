@extends('layouts.app')
@section('title', 'New Email')

@section('content')
<h1 class="h3 mb-4">New Email</h1>

@if (session('invalid_rows'))
    @include('campaigns._invalid', ['rows' => session('invalid_rows')])
@endif

<form method="POST" action="{{ route('campaigns.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4">
        <div class="card-header">1. Upload Recipients</div>
        <div class="card-body">
            <input type="file" name="recipients_file" accept=".csv,text/csv"
                   class="form-control @error('recipients_file') is-invalid @enderror" required>
            @error('recipients_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">
                CSV with a header row, e.g. <code>name,email</code> then <code>Ram Sharma,ram@example.com</code>.
                Invalid and duplicate rows are skipped and reported. Max 2 MB.
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">2. Compose Email</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="subject">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" maxlength="255"
                       class="form-control @error('subject') is-invalid @enderror" required>
                @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="body">Email body</label>
                <textarea id="body" name="body" rows="10"
                          {{-- class="form-control @error('body') is-invalid @enderror" required>{{ old('body', "Dear {{name}},\n\n\n\nRegards,\nIT Department") }}</textarea> --}}
                          class="form-control @error('body') is-invalid @enderror" required>{{ old('body', $defaultBody) }}</textarea>
                @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Plain text. Use <code>&#123;&#123;name&#125;&#125;</code> and <code>&#123;&#123;email&#125;&#125;</code> for personalization.</div>
            </div>

            <div>
                <label class="form-label" for="attachment">Attachment (optional)</label>
                <input type="file" id="attachment" name="attachment"
                       class="form-control @error('attachment') is-invalid @enderror">
                @error('attachment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">PDF, Office documents, images, txt, csv or zip. Max 5 MB.</div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary">Import &amp; Continue</button>
    <a href="{{ route('dashboard') }}" class="btn btn-link">Cancel</a>
</form>
@endsection
