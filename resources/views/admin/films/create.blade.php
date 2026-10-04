@extends('admin.layout')

@section('title', 'Add Film')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Add Film</h1>
        <p class="text-sf-muted">Search TMDB, choose a result, then review the imported details and poster.</p>
    </div>

    <div class="mb-6 rounded-xl border border-sf-border bg-sf-surface p-5">
        <label for="tmdbLookup" class="mb-2 block text-sm font-medium text-gray-200">Search TMDB</label>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input id="tmdbLookup" type="search" placeholder="Search by movie title..." class="flex-1 rounded-lg border border-sf-border bg-[#100b17] px-3 py-2 text-white placeholder:text-sf-muted focus:border-red-500 focus:ring-red-500">
            <button id="tmdbLookupBtn" type="button" class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-500">Search movies</button>
        </div>
        <p id="tmdbStatus" role="status" class="mt-3 hidden text-sm text-sf-muted"></p>
        <div id="tmdbResults" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div>
    </div>

    <div class="rounded-xl border border-sf-border bg-sf-surface p-5 sm:p-6">
        <h2 class="mb-4 text-lg font-semibold text-white">Film details</h2>
        <form method="POST" action="{{ route('admin.films.store') }}" enctype="multipart/form-data" class="grid gap-4">
            @csrf
            @include('admin.films._form')
            <div>
                <button class="rounded-lg bg-red-600 px-5 py-2.5 font-semibold text-white transition hover:bg-red-500">Save Film</button>
            </div>
        </form>
    </div>

    <script>
        const tmdbLookupBtn = document.getElementById('tmdbLookupBtn');
        const tmdbLookupInput = document.getElementById('tmdbLookup');
        const tmdbStatus = document.getElementById('tmdbStatus');
        const tmdbResults = document.getElementById('tmdbResults');
        const posterWrap = document.getElementById('posterPreviewWrap');
        const posterPreview = document.getElementById('posterPreview');
        const posterFallback = document.getElementById('posterPreviewFallback');

        function showPoster(url) {
            posterWrap.classList.remove('hidden');
            if (url) {
                posterPreview.src = url;
                posterPreview.classList.remove('hidden');
                posterFallback.classList.add('hidden');
            } else {
                posterPreview.removeAttribute('src');
                posterPreview.classList.add('hidden');
                posterFallback.classList.remove('hidden');
            }
        }

        function fillFilmForm(film) {
            document.getElementById('tmdb_id').value = film.tmdb_id ?? '';
            document.getElementById('tmdb_poster_path').value = film.poster_path ?? '';
            document.getElementById('title').value = film.title ?? '';
            document.getElementById('original_title').value = film.original_title ?? '';
            document.getElementById('synopsis').value = film.synopsis ?? '';
            document.getElementById('genre').value = film.genre ?? '';
            document.getElementById('release_date').value = film.release_date ?? '';
            document.getElementById('release_year').value = film.release_year ?? '';
            document.getElementById('cast').value = film.cast ?? '';
            showPoster(film.poster_url ?? null);
        }

        function renderResults(results) {
            tmdbResults.replaceChildren();
            for (const film of results) {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.tmdbId = film.tmdb_id;
                button.dataset.posterUrl = film.poster_url ?? '';
                button.dataset.posterPath = film.poster_path ?? '';
                button.className = 'flex gap-3 rounded-lg border border-sf-border bg-[#100b17] p-3 text-left transition hover:border-red-500/60 hover:bg-white/5';

                const imageWrap = document.createElement('div');
                imageWrap.className = 'h-24 w-16 shrink-0 overflow-hidden rounded bg-sf-bg';
                if (film.poster_url) {
                    const image = document.createElement('img');
                    image.src = film.poster_url;
                    image.alt = `${film.title} poster`;
                    image.loading = 'lazy';
                    image.className = 'h-full w-full object-cover';
                    imageWrap.append(image);
                } else {
                    imageWrap.className += ' flex items-center justify-center text-center text-[10px] text-sf-muted';
                    imageWrap.textContent = 'No poster';
                }

                const text = document.createElement('div');
                const title = document.createElement('p');
                title.className = 'font-medium text-white';
                title.textContent = film.title;
                const year = document.createElement('p');
                year.className = 'mt-1 text-sm text-sf-muted';
                year.textContent = film.release_year ?? 'Release year unavailable';
                const choose = document.createElement('p');
                choose.className = 'mt-3 text-xs font-semibold text-red-400';
                choose.textContent = 'Select film';
                text.append(title, year, choose);
                button.append(imageWrap, text);
                tmdbResults.append(button);
            }
        }

        async function searchTmdb() {
            const query = tmdbLookupInput.value.trim();
            if (query.length < 2) {
                tmdbStatus.textContent = 'Enter at least two characters to search.';
                tmdbStatus.classList.remove('hidden');
                return;
            }

            tmdbLookupBtn.disabled = true;
            tmdbStatus.textContent = 'Searching TMDB...';
            tmdbStatus.classList.remove('hidden');
            tmdbResults.replaceChildren();

            try {
                const url = new URL('{{ route('admin.films.tmdb-results') }}', window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'TMDB search failed.');
                }

                renderResults(data.results ?? []);
                tmdbStatus.textContent = data.results?.length ? 'Choose a match to fill the form.' : 'No matching films found.';
            } catch (error) {
                tmdbStatus.textContent = error.message || 'TMDB search failed.';
            } finally {
                tmdbLookupBtn.disabled = false;
            }
        }

        tmdbLookupBtn.addEventListener('click', searchTmdb);
        tmdbLookupInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchTmdb();
            }
        });

        tmdbResults.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-tmdb-id]');
            if (!button) return;

            const posterUrl = button.dataset.posterUrl || null;
            document.getElementById('tmdb_id').value = button.dataset.tmdbId;
            document.getElementById('tmdb_poster_path').value = button.dataset.posterPath || '';
            showPoster(posterUrl);
            tmdbStatus.textContent = 'Loading full movie details...';

            try {
                const detailUrl = '{{ route('admin.films.tmdb-details', ['tmdbId' => 'TMDB_ID']) }}'.replace('TMDB_ID', encodeURIComponent(button.dataset.tmdbId));
                const response = await fetch(detailUrl, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Could not load movie details.');
                }

                fillFilmForm(data);
                tmdbStatus.textContent = 'Film details loaded. Review them before saving.';
            } catch (error) {
                tmdbStatus.textContent = error.message || 'Could not load movie details.';
            }
        });
    </script>
@endsection
