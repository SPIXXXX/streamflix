@extends('admin.layout')

@section('title', 'Add Films')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Add Films</h1>
        <p class="text-sf-muted">Search TMDB, add the films you want, then review and edit each one before saving.</p>
    </div>

    <section class="mb-6 rounded-xl border border-sf-border bg-sf-surface p-5" aria-labelledby="tmdb-search-title">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="tmdb-search-title" class="text-lg font-semibold text-white">Find films on TMDB</h2>
                <p class="mt-1 text-sm text-sf-muted">Search again after adding results to build your batch. Up to 20 films per save.</p>
            </div>
            <span id="queueCount" class="rounded-full bg-white/5 px-3 py-1 text-sm text-sf-muted">0 selected</span>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input id="tmdbLookup" type="search" placeholder="Search by movie title..." class="flex-1 rounded-lg border border-sf-border bg-[#100b17] px-3 py-2 text-white placeholder:text-sf-muted focus:border-red-500 focus:ring-red-500">
            <button id="tmdbLookupBtn" type="button" class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-500 disabled:cursor-wait disabled:opacity-60">Search movies</button>
        </div>
        <p id="tmdbStatus" role="status" aria-live="polite" class="mt-3 hidden text-sm text-sf-muted"></p>
        <div id="tmdbResults" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4"></div>
    </section>

    <section class="rounded-xl border border-sf-border bg-sf-surface p-5 sm:p-6" aria-labelledby="review-films-title">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="review-films-title" class="text-lg font-semibold text-white">Review selected films</h2>
                <p class="mt-1 text-sm text-sf-muted">Check the TMDB details and make edits before adding them to your library.</p>
            </div>
        </div>
        <form id="bulkFilmForm" method="POST" action="{{ route('admin.films.bulk-store') }}">
            @csrf
            <input id="filmsPayload" type="hidden" name="films" value="">
            <div id="reviewQueue" class="space-y-4">
                <p id="emptyQueue" class="rounded-lg border border-dashed border-sf-border p-6 text-center text-sm text-sf-muted">No films selected yet. Search TMDB and add films to review them here.</p>
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-sf-border pt-4">
                <p id="saveHint" class="text-sm text-sf-muted">Select at least one film to enable saving.</p>
                <button id="saveFilmsBtn" type="submit" disabled class="rounded-lg bg-red-600 px-5 py-2.5 font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-50">Save selected films</button>
            </div>
        </form>
    </section>

    <script>
        const tmdbLookupBtn = document.getElementById('tmdbLookupBtn');
        const tmdbLookupInput = document.getElementById('tmdbLookup');
        const tmdbStatus = document.getElementById('tmdbStatus');
        const tmdbResults = document.getElementById('tmdbResults');
        const reviewQueue = document.getElementById('reviewQueue');
        const emptyQueue = document.getElementById('emptyQueue');
        const queueCount = document.getElementById('queueCount');
        const saveFilmsBtn = document.getElementById('saveFilmsBtn');
        const filmsPayload = document.getElementById('filmsPayload');
        const films = new Map();
        const detailUrlTemplate = @json(route('admin.films.tmdb-details', ['tmdbId' => 'TMDB_ID']));

        function setStatus(message, visible = true) {
            tmdbStatus.textContent = message;
            tmdbStatus.classList.toggle('hidden', !visible);
        }

        function renderResults(results) {
            tmdbResults.replaceChildren();
            for (const film of results) {
                const card = document.createElement('article');
                card.className = 'flex gap-3 rounded-lg border border-sf-border bg-[#100b17] p-3';
                const imageWrap = document.createElement('div');
                imageWrap.className = 'flex h-28 w-[4.5rem] shrink-0 items-center justify-center overflow-hidden rounded bg-sf-bg text-center text-[10px] text-sf-muted';
                if (film.poster_url) {
                    const image = document.createElement('img');
                    image.src = film.poster_url;
                    image.alt = `${film.title} poster`;
                    image.loading = 'lazy';
                    image.className = 'h-full w-full object-cover';
                    imageWrap.append(image);
                } else imageWrap.textContent = 'No poster';

                const details = document.createElement('div');
                details.className = 'flex min-w-0 flex-1 flex-col items-start';
                const title = document.createElement('p');
                title.className = 'line-clamp-2 font-medium text-white';
                title.textContent = film.title;
                const year = document.createElement('p');
                year.className = 'mt-1 text-sm text-sf-muted';
                year.textContent = film.release_year || 'Year unavailable';
                const add = document.createElement('button');
                add.type = 'button';
                add.dataset.tmdbId = film.tmdb_id;
                add.dataset.posterPath = film.poster_path || '';
                add.className = 'mt-auto pt-3 text-left text-xs font-semibold text-red-400 hover:text-red-300';
                add.textContent = films.has(String(film.tmdb_id)) ? 'Already selected' : '＋ Add to review';
                add.disabled = films.has(String(film.tmdb_id));
                add.classList.toggle('opacity-50', add.disabled);
                details.append(title, year, add);
                card.append(imageWrap, details);
                tmdbResults.append(card);
            }
        }

        function updateQueue() {
            reviewQueue.replaceChildren();
            emptyQueue.classList.toggle('hidden', films.size > 0);
            reviewQueue.append(emptyQueue);
            queueCount.textContent = `${films.size} selected`;
            saveFilmsBtn.disabled = films.size === 0;
            document.getElementById('saveHint').textContent = films.size
                ? `${films.size} film${films.size === 1 ? '' : 's'} ready to save after review.`
                : 'Select at least one film to enable saving.';
            filmsPayload.value = JSON.stringify([...films.values()]);

            for (const [tmdbId, film] of films) {
                const card = document.createElement('article');
                card.className = 'rounded-xl border border-sf-border bg-[#100b17] p-4';
                const heading = document.createElement('div');
                heading.className = 'mb-4 flex items-start justify-between gap-3';
                const identity = document.createElement('div');
                const name = document.createElement('h3');
                name.className = 'font-semibold text-white';
                name.textContent = film.title || 'Untitled film';
                const id = document.createElement('p');
                id.className = 'mt-1 text-xs text-sf-muted';
                id.textContent = `TMDB #${tmdbId}`;
                identity.append(name, id);
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.dataset.removeId = tmdbId;
                remove.className = 'shrink-0 rounded-md px-2 py-1 text-sm text-red-300 hover:bg-red-500/10';
                remove.textContent = 'Remove';
                heading.append(identity, remove);

                const grid = document.createElement('div');
                grid.className = 'grid gap-3 md:grid-cols-2';
                const fields = [
                    ['title', 'Title', 'text', 'md:col-span-2'],
                    ['original_title', 'Original title', 'text', ''],
                    ['genre', 'Genre', 'text', ''],
                    ['release_date', 'Release date', 'date', ''],
                    ['release_year', 'Release year', 'number', ''],
                ];
                for (const [key, label, type, span] of fields) {
                    const wrapper = document.createElement('label');
                    wrapper.className = `block ${span}`;
                    const caption = document.createElement('span');
                    caption.className = 'mb-1 block text-xs font-medium text-sf-muted';
                    caption.textContent = label;
                    const input = document.createElement('input');
                    input.type = type;
                    input.value = film[key] ?? '';
                    input.dataset.field = key;
                    input.className = 'w-full rounded-lg border border-white/10 bg-[#160c1f] px-3 py-2 text-sm text-white focus:border-red-500 focus:outline-none';
                    if (key === 'title') input.required = true;
                    if (key === 'release_year') { input.min = '1900'; input.max = String(new Date().getFullYear() + 5); }
                    wrapper.append(caption, input);
                    grid.append(wrapper);
                }
                const synopsis = document.createElement('label');
                synopsis.className = 'mt-3 block';
                const synopsisLabel = document.createElement('span');
                synopsisLabel.className = 'mb-1 block text-xs font-medium text-sf-muted';
                synopsisLabel.textContent = 'Synopsis';
                const synopsisInput = document.createElement('textarea');
                synopsisInput.rows = 3;
                synopsisInput.dataset.field = 'synopsis';
                synopsisInput.value = film.synopsis ?? '';
                synopsisInput.className = 'w-full rounded-lg border border-white/10 bg-[#160c1f] px-3 py-2 text-sm text-white focus:border-red-500 focus:outline-none';
                synopsis.append(synopsisLabel, synopsisInput);
                const cast = document.createElement('label');
                cast.className = 'mt-3 block';
                const castLabel = document.createElement('span');
                castLabel.className = 'mb-1 block text-xs font-medium text-sf-muted';
                castLabel.textContent = 'Cast';
                const castInput = document.createElement('input');
                castInput.type = 'text';
                castInput.dataset.field = 'cast';
                castInput.value = film.cast ?? '';
                castInput.className = 'w-full rounded-lg border border-white/10 bg-[#160c1f] px-3 py-2 text-sm text-white focus:border-red-500 focus:outline-none';
                cast.append(castLabel, castInput);
                card.append(heading, grid, synopsis, cast);
                reviewQueue.append(card);
            }
        }

        async function searchTmdb() {
            const query = tmdbLookupInput.value.trim();
            if (query.length < 2) return setStatus('Enter at least two characters to search.');
            tmdbLookupBtn.disabled = true;
            setStatus('Searching TMDB...');
            tmdbResults.replaceChildren();
            try {
                const url = new URL(@json(route('admin.films.tmdb-results')), window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'TMDB search failed.');
                renderResults(data.results || []);
                setStatus(data.results?.length ? 'Add the matches you want, then search for another title.' : 'No matching films found.');
            } catch (error) {
                setStatus(error.message || 'TMDB search failed.');
            } finally { tmdbLookupBtn.disabled = false; }
        }

        tmdbLookupBtn.addEventListener('click', searchTmdb);
        tmdbLookupInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') { event.preventDefault(); searchTmdb(); }
        });

        tmdbResults.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-tmdb-id]');
            if (!button || button.disabled || films.size >= 20) {
                if (films.size >= 20) setStatus('You can review up to 20 films in one batch.');
                return;
            }
            const tmdbId = String(button.dataset.tmdbId);
            button.disabled = true;
            button.textContent = 'Loading details...';
            try {
                const detailUrl = detailUrlTemplate.replace('TMDB_ID', encodeURIComponent(tmdbId));
                const response = await fetch(detailUrl, { headers: { Accept: 'application/json' } });
                const detail = await response.json();
                if (!response.ok) throw new Error(detail.message || 'Could not load film details.');
                if (films.has(tmdbId)) return;
                films.set(tmdbId, {
                    tmdb_id: Number(detail.tmdb_id), title: detail.title || '', original_title: detail.original_title || '',
                    synopsis: detail.synopsis || '', genre: detail.genre || '', release_date: detail.release_date || '',
                    release_year: detail.release_year || '', cast: detail.cast || '', poster_path: detail.poster_path || button.dataset.posterPath || '',
                });
                button.textContent = 'Already selected';
                updateQueue();
                setStatus(`${detail.title} added to the review queue.`);
            } catch (error) {
                button.disabled = false;
                button.textContent = '＋ Add to review';
                setStatus(error.message || 'Could not load film details.');
            }
        });

        reviewQueue.addEventListener('input', (event) => {
            const field = event.target.dataset.field;
            if (!field) return;
            const card = event.target.closest('article');
            const tmdbId = card.querySelector('[data-remove-id]').dataset.removeId;
            films.get(tmdbId)[field] = event.target.value;
            const heading = card.querySelector('h3');
            if (field === 'title') heading.textContent = event.target.value || 'Untitled film';
            filmsPayload.value = JSON.stringify([...films.values()]);
        });

        reviewQueue.addEventListener('click', (event) => {
            const remove = event.target.closest('button[data-remove-id]');
            if (!remove) return;
            films.delete(remove.dataset.removeId);
            const resultButton = tmdbResults.querySelector(`button[data-tmdb-id="${CSS.escape(remove.dataset.removeId)}"]`);
            if (resultButton) { resultButton.disabled = false; resultButton.textContent = '＋ Add to review'; }
            updateQueue();
        });

        document.getElementById('bulkFilmForm').addEventListener('submit', (event) => {
            for (const film of films.values()) {
                if (!film.title.trim()) {
                    event.preventDefault();
                    setStatus('Every selected film needs a title before saving.');
                    reviewQueue.querySelector(`[data-remove-id="${CSS.escape(String(film.tmdb_id))}"]`)?.closest('article').querySelector('[data-field="title"]')?.focus();
                    return;
                }
            }
            filmsPayload.value = JSON.stringify([...films.values()]);
            saveFilmsBtn.disabled = true;
            saveFilmsBtn.textContent = 'Saving films...';
        });

        updateQueue();
    </script>
@endsection
