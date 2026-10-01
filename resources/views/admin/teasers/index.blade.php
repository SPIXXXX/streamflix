@extends('admin.layout')

@section('title', 'Manage Teasers')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Manage Teasers</h1>
        <a href="{{ route('admin.teasers.create') }}" class="px-4 py-2 rounded-lg bg-sf-blue text-white">+ Add Teaser</a>
    </div>

    <div class="bg-sf-surface border border-sf-border rounded-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-sf-surface/50">
                <tr>
                    <th class="px-4 py-3 text-xs text-sf-muted uppercase">Film</th>
                    <th class="px-4 py-3 text-xs text-sf-muted uppercase">Release Date</th>
                    <th class="px-4 py-3 text-xs text-sf-muted uppercase"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sf-border">
                @foreach ($teasers as $teaser)
                    <tr>
                        <td class="px-4 py-3">{{ $teaser->film->title }}</td>
                        <td class="px-4 py-3">{{ $teaser->release_date?->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-3 items-center">
                                <a href="{{ route('admin.teasers.edit', $teaser) }}" class="text-sf-muted hover:text-white">Edit</a>
                                <form action="{{ route('admin.teasers.destroy', $teaser) }}" method="POST" data-confirm data-confirm-title="Delete this teaser?" data-confirm-message="This movie teaser will be permanently removed." data-confirm-label="Delete teaser">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $teasers->links() }}</div>
@endsection
