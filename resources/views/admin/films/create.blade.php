@extends('admin.layout')

@section('title', 'Add Films')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Add Films</h1>
        <p class="text-sf-muted">Search TMDB and click films to select them. Review each selected film before saving the batch.</p>
    </div>

    <section class="mb-6 rounded-xl border border-sf-border bg-sf-surface p-5" aria-labelledby="tmdb-search-title">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="tmdb-search-title" class="text-lg font-semibold text-white">Find films on TMDB</h2>
                <p class="mt-1 text-sm text-sf-muted">Click a result to select it. Search again to add more films, up to 20 per batch.</p>
            </div>
            <span id="queueCount" class="rounded-full bg-white/5 px-3 py-1 text-sm text-sf-muted">0 selected</span>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input id="tmdbLookup" type="search" placeholder="Search by movie title..." class="flex-1 rounded-lg border border-sf-border bg-[#182337] px-3 py-2 text-white placeholder:text-sf-muted focus:border-red-500 focus:ring-red-500">
            <button id="tmdbLookupBtn" type="button" class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-500 disabled:cursor-wait disabled:opacity-60">Search movies</button>
        </div>
        <p id="tmdbStatus" role="status" aria-live="polite" class="mt-3 hidden text-sm text-sf-muted"></p>
        <div id="tmdbResults" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4"></div>
    </section>

    <section class="rounded-xl border border-sf-border bg-sf-surface p-5 sm:p-6" aria-labelledby="review-films-title">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="review-films-title" class="text-lg font-semibold text-white">Review selected films</h2>
                <p class="mt-1 text-sm text-sf-muted">Scroll through the selected poster cards, choose a film, and review its poster and details below.</p>
            </div>
        </div>
        <form id="bulkFilmForm" method="POST" action="{{ route('admin.films.bulk-store') }}">
            @csrf
            <input id="filmsPayload" type="hidden" name="films" value="">
            <div id="reviewQueue">
                <p id="emptyQueue" class="rounded-lg border border-dashed border-sf-border p-6 text-center text-sm text-sf-muted">No films selected yet. Click a TMDB result to start your review queue.</p>
                <div id="reviewCarousel" class="-mx-1 hidden gap-3 overflow-x-auto px-1 pb-3" role="list" aria-label="Selected films"></div>
                <div id="activeFilmReview" class="mt-4 hidden"></div>
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
        const reviewCarousel = document.getElementById('reviewCarousel');
        const activeFilmReview = document.getElementById('activeFilmReview');
        const queueCount = document.getElementById('queueCount');
        const saveFilmsBtn = document.getElementById('saveFilmsBtn');
        const filmsPayload = document.getElementById('filmsPayload');
        const films = new Map();
        let activeFilmId = null;
        const detailUrlTemplate = @json(route('admin.films.tmdb-details', ['tmdbId' => 'TMDB_ID']));

        function setStatus(message, visible = true) {
            tmdbStatus.textContent = message;
            tmdbStatus.classList.toggle('hidden', !visible);
        }

        function setSearching(searching) {
            tmdbLookupBtn.disabled = searching;
            tmdbLookupBtn.textContent = searching ? 'Searching TMDB…' : 'Search movies';
            if (searching) {
                tmdbLookupBtn.dataset.loading = 'true';
                tmdbLookupBtn.setAttribute('aria-busy', 'true');
            } else {
                delete tmdbLookupBtn.dataset.loading;
                tmdbLookupBtn.removeAttribute('aria-busy');
            }
        }

        function renderResults(results) {
            tmdbResults.replaceChildren();
            for (const film of results) {
                const id = String(film.tmdb_id);
                const selected = films.has(id);
                const card = document.createElement('button');
                card.type = 'button';
                card.dataset.tmdbId = id;
                card.dataset.posterPath = film.poster_path || '';
                card.dataset.posterUrl = film.poster_url || '';
                card.setAttribute('aria-pressed', selected ? 'true' : 'false');
                card.className = `flex w-full gap-3 rounded-lg border p-3 text-left transition hover:border-red-400/70 ${selected ? 'border-red-500 bg-red-950/30 ring-1 ring-red-500/50' : 'border-sf-border bg-[#182337]'}`;
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
                const hint = document.createElement('p');
                hint.dataset.selectionHint = 'true';
                hint.className = 'mt-auto pt-3 text-xs font-semibold';
                hint.classList.add(selected ? 'text-red-300' : 'text-sf-muted');
                hint.textContent = selected ? '✓ Selected · click to review' : 'Click to select';
                details.append(title, year, hint);
                card.append(imageWrap, details);
                tmdbResults.append(card);
            }
        }

        function syncPayload() {
            filmsPayload.value = JSON.stringify([...films.values()]);
        }

        function updateQueue() {
            emptyQueue.classList.toggle('hidden', films.size > 0);
            reviewCarousel.classList.toggle('hidden', films.size === 0);
            reviewCarousel.classList.toggle('flex', films.size > 0);
            queueCount.textContent = `${films.size} selected`;
            saveFilmsBtn.disabled = films.size === 0;
            document.getElementById('saveHint').textContent = films.size
                ? `${films.size} film${films.size === 1 ? '' : 's'} ready to save after review.`
                : 'Select at least one film to enable saving.';
            syncPayload();
            renderCarousel();
            renderActiveReview();
            updateResultHighlights();
        }

        function updateResultHighlights() {
            tmdbResults.querySelectorAll('button[data-tmdb-id]').forEach((button) => {
                const selected = films.has(button.dataset.tmdbId);
                button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                button.classList.toggle('border-red-500', selected);
                button.classList.toggle('bg-red-950/30', selected);
                button.classList.toggle('ring-1', selected);
                button.classList.toggle('ring-red-500/50', selected);
                button.classList.toggle('border-sf-border', !selected);
                const hint = button.querySelector('[data-selection-hint]');
                if (hint) {
                    hint.textContent = selected ? '✓ Selected · click to review' : 'Click to select';
                    hint.classList.toggle('text-red-300', selected);
                    hint.classList.toggle('text-sf-muted', !selected);
                }
            });
        }

        function renderCarousel() {
            reviewCarousel.replaceChildren();
            for (const [tmdbId, film] of films) {
                const wrapper = document.createElement('div');
                wrapper.id = `review-film-${tmdbId}`;
                wrapper.className = 'w-32 shrink-0 sm:w-36';
                const select = document.createElement('button');
                select.type = 'button';
                select.dataset.reviewId = tmdbId;
                select.setAttribute('aria-pressed', activeFilmId === tmdbId ? 'true' : 'false');
                select.className = `block w-full overflow-hidden rounded-xl border text-left transition ${activeFilmId === tmdbId ? 'border-red-500 ring-2 ring-red-500/50' : 'border-sf-border hover:border-white/40'}`;
                const poster = document.createElement('div');
                poster.className = 'flex aspect-[2/3] items-center justify-center overflow-hidden bg-[#182337] text-xs text-sf-muted';
                if (film.poster_url) {
                    const image = document.createElement('img');
                    image.src = film.poster_url;
                    image.alt = `${film.title} poster`;
                    image.loading = 'lazy';
                    image.className = 'h-full w-full object-cover';
                    poster.append(image);
                } else poster.textContent = 'No poster';
                const name = document.createElement('span');
                name.dataset.cardTitle = tmdbId;
                name.className = 'block truncate bg-[#182337] px-2 py-2 text-xs font-medium text-white';
                name.textContent = film.title || 'Untitled film';
                select.append(poster, name);
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.dataset.removeId = tmdbId;
                remove.className = 'mt-1 w-full rounded-md px-2 py-1 text-xs text-red-300 hover:bg-red-500/10';
                remove.textContent = 'Remove';
                wrapper.append(select, remove);
                reviewCarousel.append(wrapper);
            }
        }

        function makeField(film, key, label, type, span = '') {
            const wrapper = document.createElement('label');
            wrapper.className = `block ${span}`;
            const caption = document.createElement('span');
            caption.className = 'mb-1 block text-xs font-medium text-sf-muted';
            caption.textContent = label;
            const input = document.createElement(type === 'textarea' ? 'textarea' : 'input');
            if (type === 'textarea') input.rows = 5;
            else input.type = type;
            input.value = film[key] ?? '';
            input.dataset.field = key;
            input.className = 'w-full rounded-lg border border-white/10 bg-[#182337] px-3 py-2 text-sm text-white focus:border-red-500 focus:outline-none';
            if (key === 'title') input.required = true;
            if (key === 'release_year') { input.min = '1900'; input.max = String(new Date().getFullYear() + 5); }
            wrapper.append(caption, input);
            return wrapper;
        }

        function renderActiveReview() {
            activeFilmReview.replaceChildren();
            const film = films.get(activeFilmId);
            activeFilmReview.classList.toggle('hidden', !film);
            if (!film) return;

            const layout = document.createElement('div');
            layout.className = 'grid gap-5 rounded-xl border border-sf-border bg-[#182337] p-4 lg:grid-cols-[220px_minmax(0,1fr)] sm:p-5';
            const posterColumn = document.createElement('div');
            const posterHeading = document.createElement('p');
            posterHeading.className = 'mb-2 text-sm font-medium text-sf-muted';
            posterHeading.textContent = 'Poster preview';
            const posterFrame = document.createElement('div');
            posterFrame.className = 'mx-auto flex aspect-[2/3] w-full max-w-[220px] items-center justify-center overflow-hidden rounded-lg border border-white/10 bg-[#182337] text-sm text-sf-muted';
            if (film.poster_url) {
                const image = document.createElement('img');
                image.src = film.poster_url;
                image.alt = `${film.title} poster preview`;
                image.className = 'h-full w-full object-cover';
                posterFrame.append(image);
            } else posterFrame.textContent = 'No poster available';
            posterColumn.append(posterHeading, posterFrame);

            const details = document.createElement('div');
            const heading = document.createElement('div');
            heading.className = 'mb-4';
            const title = document.createElement('h3');
            title.className = 'text-lg font-semibold text-white';
            title.textContent = film.title || 'Untitled film';
            const tmdb = document.createElement('p');
            tmdb.className = 'mt-1 text-xs text-sf-muted';
            tmdb.textContent = `TMDB #${film.tmdb_id}`;
            heading.append(title, tmdb);
            const fields = document.createElement('div');
            fields.className = 'grid gap-3 md:grid-cols-2';
            fields.append(
                makeField(film, 'title', 'Title', 'text', 'md:col-span-2'),
                makeField(film, 'original_title', 'Original title', 'text'),
                makeField(film, 'genre', 'Genre', 'text'),
                makeField(film, 'release_date', 'Release date', 'date'),
                makeField(film, 'release_year', 'Release year', 'number'),
                makeField(film, 'synopsis', 'Synopsis', 'textarea', 'md:col-span-2'),
                makeField(film, 'cast', 'Cast', 'text', 'md:col-span-2'),
            );
            details.append(heading, fields);
            layout.append(posterColumn, details);
            activeFilmReview.append(layout);
        }

        async function searchTmdb() {
            const query = tmdbLookupInput.value.trim();
            if (query.length < 2) return setStatus('Enter at least two characters to search.');
            setSearching(true);
            setStatus('Searching TMDB...');
            tmdbResults.replaceChildren();
            try {
                const url = new URL(@json(route('admin.films.tmdb-results')), window.location.origin);
                url.searchParams.set('q', query);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'TMDB search failed.');
                renderResults(data.results || []);
                setStatus(data.results?.length ? 'Click a result to select it, then search another title to add more.' : 'No matching films found.');
            } catch (error) {
                setStatus(error.message || 'TMDB search failed.');
            } finally { setSearching(false); }
        }

        tmdbLookupBtn.addEventListener('click', searchTmdb);
        tmdbLookupInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') { event.preventDefault(); searchTmdb(); }
        });

        tmdbResults.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-tmdb-id]');
            if (!button || button.disabled) return;
            const tmdbId = String(button.dataset.tmdbId);
            if (films.has(tmdbId)) {
                activeFilmId = tmdbId;
                updateQueue();
                document.getElementById(`review-film-${tmdbId}`)?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                return;
            }
            if (films.size >= 20) return setStatus('You can review up to 20 films in one batch.');
            button.disabled = true;
            setStatus('Loading full TMDB details...');
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
                    poster_url: detail.poster_url || button.dataset.posterUrl || '',
                });
                activeFilmId = tmdbId;
                button.disabled = false;
                updateQueue();
                setStatus(`${detail.title} selected. Review its poster and details below.`);
            } catch (error) {
                button.disabled = false;
                setStatus(error.message || 'Could not load film details.');
            }
        });

        reviewQueue.addEventListener('input', (event) => {
            const field = event.target.dataset.field;
            if (!field) return;
            const film = films.get(activeFilmId);
            if (!film) return;
            film[field] = event.target.value;
            if (field === 'title') {
                activeFilmReview.querySelector('h3').textContent = event.target.value || 'Untitled film';
                const cardTitle = reviewCarousel.querySelector(`[data-card-title="${CSS.escape(activeFilmId)}"]`);
                if (cardTitle) cardTitle.textContent = event.target.value || 'Untitled film';
            }
            syncPayload();
        });

        reviewQueue.addEventListener('click', (event) => {
            const select = event.target.closest('button[data-review-id]');
            if (select) {
                activeFilmId = select.dataset.reviewId;
                const selectedId = activeFilmId;
                updateQueue();
                document.getElementById(`review-film-${selectedId}`)?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                return;
            }
            const remove = event.target.closest('button[data-remove-id]');
            if (!remove) return;
            const removedId = remove.dataset.removeId;
            films.delete(removedId);
            if (activeFilmId === removedId) activeFilmId = films.keys().next().value || null;
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
