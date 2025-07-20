@extends('layout')

@section('content')
<div class="container">
    <h2>Edit Message</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('message-board.update', $message->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $message->title) }}" required>
        </div>

        <div class="mb-3">
            <label for="body" class="form-label">Message</label>
            <textarea id="body"  id="content" name="content" class="form-control" rows="5" required>{{ old('body', $message->content) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Message</button>
        <a href="{{ route('message-board.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
