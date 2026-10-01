@switch($activity->type)
    @case('review')
        Reviewed <span class="font-semibold text-white">{{ $activity->subject }}</span> and rated it <span class="font-semibold text-amber-300">{{ $activity->detail }} / 5</span>.
        @break
    @case('list')
        Created the public list <span class="font-semibold text-white">{{ $activity->subject }}</span>.
        @break
    @case('favorite')
        Saved <span class="font-semibold text-white">{{ $activity->subject }}</span> as a favorite.
        @break
    @case('reaction')
        Received an <span class="font-semibold text-white">{{ $activity->detail }}</span> reaction on a review of <span class="font-semibold text-white">{{ $activity->subject }}</span>.
        @break
    @case('list_add')
        Added <span class="font-semibold text-white">{{ $activity->subject }}</span> to the public list <span class="font-semibold text-white">{{ $activity->context }}</span>.
        @break
@endswitch
