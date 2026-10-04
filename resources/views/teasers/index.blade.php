<x-app-layout>
    <div class="max-w-6xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold text-white mb-6">Upcoming Films</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @forelse ($teasers as $teaser)
                <div class="bg-sf-surface rounded-lg overflow-hidden shadow-lg hover:shadow-red-900/30 transition">
                    <div class="aspect-video bg-sf-bg relative">
                        <iframe src="{{ $teaser->video_url }}" class="w-full h-full" allowfullscreen></iframe>
                    </div>
                    <div class="p-4">
                        <h2 class="text-white font-semibold">{{ $teaser->film->title }}</h2>
                        @if ($teaser->release_date)
                            <p class="text-sf-muted text-sm">{{ $teaser->release_date->format('M d, Y') }}</p>
                        @endif
                        <p class="text-gray-400 text-sm mt-2">{{ $teaser->description }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 sm:col-span-2 md:col-span-3">No upcoming films have teasers yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $teasers->links() }}</div>
    </div>
</x-app-layout>
