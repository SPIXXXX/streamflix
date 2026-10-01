import Alpine from 'alpinejs';

Alpine.data('adminListFilmPicker', (config) => ({
    endpoint: config.endpoint,
    metadataEndpoint: config.metadataEndpoint,
    classificationType: config.classificationType || '',
    classifications: config.classifications || (config.classification ? [config.classification] : []),
    genres: config.genres || [],
    themes: config.themes || [],
    allowTypeSelection: config.allowTypeSelection || false,
    listId: config.listId || null,
    matchingOnly: config.matchingOnly ?? true,
    search: config.search || '',
    filter: 'all',
    sortBy: 'title',
    films: [],
    selected: Object.fromEntries((config.selected || []).map((film) => [String(film.id), film])),
    loading: false,
    error: '',
    requestController: null,
    currentPage: 0,
    lastPage: 1,
    meta: { total: 0 },
    metadataByFilm: {},
    metadataLoading: {},
    saving: false,

    get selectedFilms() {
        return Object.values(this.selected);
    },

    get visibleFilms() {
        let films = this.filter === 'selected'
            ? this.selectedFilms
            : (this.filter === 'unselected' ? this.films.filter((film) => !this.isSelected(film)) : this.films);
        const query = this.search.trim().toLocaleLowerCase();
        if (this.filter === 'selected' && query) films = films.filter((film) => film.title.toLocaleLowerCase().includes(query));
        return [...films].sort((a, b) => {
            if (this.sortBy === 'rating') return (Number(b.rating) || 0) - (Number(a.rating) || 0) || a.title.localeCompare(b.title);
            if (this.sortBy === 'release_date') return String(b.year || '').localeCompare(String(a.year || '')) || a.title.localeCompare(b.title);
            return a.title.localeCompare(b.title);
        });
    },

    classificationChanged() {
        this.classifications = [];
        this.matchingOnly = true;
        this.load();
    },

    toggleClassification(value) {
        this.classifications = this.classifications.includes(value)
            ? this.classifications.filter((item) => item !== value)
            : [...this.classifications, value];
        this.matchingOnly = this.classifications.length > 0;
        this.load();
    },

    removeClassification(value) { this.toggleClassification(value); },

    async load(append = false) {
        this.error = '';
        const query = this.search.trim();
        if (query.length > 0 && query.length < 2) {
            this.requestController?.abort();
            this.requestController = null;
            this.loading = false;
            this.films = [];
            this.meta = { total: 0 };
            this.currentPage = 0;
            this.lastPage = 1;
            return;
        }

        this.requestController?.abort();
        const requestController = new AbortController();
        this.requestController = requestController;
        this.loading = true;
        const page = append ? this.currentPage + 1 : 1;
        const url = new URL(this.endpoint, window.location.origin);
        if (this.classificationType) url.searchParams.set('classification_type', this.classificationType);
        this.classifications.forEach((classification) => url.searchParams.append('classifications[]', classification));
        if (this.search.trim()) url.searchParams.set('q', this.search.trim());
        if (this.listId) url.searchParams.set('list_id', this.listId);
        if (this.matchingOnly && this.classifications.length > 0) url.searchParams.set('matching', '1');
        url.searchParams.set('page', String(page));

        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin', signal: requestController.signal });
            const payload = await response.json();
            if (!response.ok) {
                const validationMessage = Object.values(payload.errors || {}).flat()[0];
                throw new Error(validationMessage || payload.message || `Unable to load movies (HTTP ${response.status}).`);
            }
            this.films = append ? [...this.films, ...(payload.films || [])] : (payload.films || []);
            this.meta = payload.meta || { total: this.films.length };
            this.currentPage = this.meta.current_page || 1;
            this.lastPage = this.meta.last_page || 1;
        } catch (error) {
            if (error.name === 'AbortError') return;
            this.error = error.message || 'Unable to load movies from the website catalog.';
            this.films = [];
        } finally {
            if (this.requestController === requestController) this.loading = false;
        }
    },

    loadMore() { return this.load(true); },

    clearSelection() { this.selected = {}; },

    toggle(film) {
        const key = String(film.id);
        if (this.selected[key]) {
            delete this.selected[key];
        } else {
            this.selected[key] = film;
            this.loadMetadata(film);
        }
    },

    async loadMetadata(film) {
        const key = String(film.id);
        if (this.metadataByFilm[key] || this.metadataLoading[key]) return;
        if (!film.tmdb_id || !this.metadataEndpoint) {
            this.metadataByFilm[key] = { available: false };
            return;
        }
        this.metadataLoading[key] = true;
        this.metadataByFilm[key] = { loading: true };
        try {
            const url = this.metadataEndpoint.replace('FILM_ID', encodeURIComponent(key));
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const metadata = await response.json();
            this.metadataByFilm[key] = response.ok && metadata.available
                ? { ...metadata, loading: false }
                : { available: false, loading: false };
            if (metadata.available) film.tmdb_keywords = (metadata.keywords || []).map((keyword) => keyword.name);
        } catch {
            this.metadataByFilm[key] = { available: false, loading: false };
        } finally {
            this.metadataLoading[key] = false;
        }
    },

    useTheme(keyword) {
        this.classificationType = 'theme';
        this.classifications = [...new Set([...this.classifications, keyword])];
        this.matchingOnly = true;
        this.load();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    isSelected(film) {
        return Boolean(this.selected[String(film.id)]);
    },

    get classificationOptions() { return this.classificationType === 'genre' ? this.genres : this.themes; },
}));
