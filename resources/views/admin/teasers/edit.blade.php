@extends('admin.layout')

@section('title', 'Edit Teaser')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Teaser</h1>
    </div>

    <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
        <form method="POST" action="{{ route('admin.teasers.update', $teaser) }}" class="grid gap-4">
            @csrf @method('PUT')
            @include('admin.teasers._form')
            <div>
                <button class="px-4 py-2 rounded-lg bg-sf-blue text-white">Update Teaser</button>
            </div>
        </form>
    </div>
@endsection
