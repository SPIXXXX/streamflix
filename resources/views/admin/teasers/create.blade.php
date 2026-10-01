@extends('admin.layout')

@section('title', 'Add Teaser')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Add Teaser</h1>
    </div>

    <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
        <form method="POST" action="{{ route('admin.teasers.store') }}" class="grid gap-4">
            @csrf
            @include('admin.teasers._form')
            <div>
                <button class="px-4 py-2 rounded-lg bg-sf-blue text-white">Save Teaser</button>
            </div>
        </form>
    </div>
@endsection
