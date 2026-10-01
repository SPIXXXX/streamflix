@extends('admin.layout')

@section('title', 'Edit film')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit film</h1>
        <p class="text-sf-muted">Update an existing movie entry.</p>
    </div>

    <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
        <form method="POST" action="{{ route('admin.films.update', $film) }}" enctype="multipart/form-data" class="grid gap-4">
            @csrf @method('PUT')
            @include('admin.films._form')
            <div>
                <button class="px-4 py-2 rounded-lg bg-sf-blue text-white">Update Film</button>
            </div>
        </form>
    </div>
@endsection
