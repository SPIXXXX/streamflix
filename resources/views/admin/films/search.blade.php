<div class="sf-admin-shell">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap');
        .sf-admin-shell { min-height:100vh; background:radial-gradient(1200px 550px at 12% -10%, rgba(184,0,31,0.15), transparent 60%), radial-gradient(1000px 500px at 100% 0%, rgba(80,118,135,0.18), transparent 55%), #0f1726; color:#FCFAEE; font-family:'Inter', system-ui, sans-serif; }
        .sf-topbar { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; padding:18px 40px 20px; border-bottom:1px solid rgba(255,255,255,0.06); background:rgba(24,35,55,0.94); backdrop-filter:blur(10px); position:sticky; top:0; z-index:20; }
        .sf-brand { display:flex; align-items:center; gap:12px; }
        .sf-brand-mark { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:rgba(184,0,31,0.18); color:#FCFAEE; }
        .sf-brand-name { font-family:'Fraunces',serif; font-size:1.4rem; font-weight:700; letter-spacing:0.02em; }
        .sf-brand-name span { color:#D4DFDF; }
        .sf-badge { font-size:0.64rem; letter-spacing:0.14em; color:#D4DFDF; border:1px solid rgba(255,255,255,0.06); border-radius:999px; padding:4px 10px; text-transform:uppercase; }
        .sf-nav { display:flex; align-items:center; gap:28px; flex-wrap:wrap; }
        .sf-nav a { position:relative; font-size:0.9rem; color:#D4DFDF; padding:6px 0; text-decoration:none; }
        .sf-nav a.active { color:#ffffff; }
        .sf-nav a.active::after { content:""; position:absolute; left:0; right:0; bottom:-14px; height:2px; background:#B8001F; }
        .sf-tools { display:flex; align-items:center; gap:16px; }
        .sf-search-pill { display:flex; align-items:center; gap:8px; padding:8px 14px; border-radius:999px; border:1px solid rgba(255,255,255,0.06); color:#D4DFDF; font-size:0.82rem; }
        .sf-icon { width:36px; height:36px; border-radius:50%; border:1px solid transparent; display:flex; align-items:center; justify-content:center; color:#D4DFDF; }
        .sf-admin-main { max-width:1100px; margin:0 auto; padding:42px 40px 80px; }
        .sf-hero { display:flex; align-items:flex-end; justify-content:space-between; gap:20px; flex-wrap:wrap; margin-bottom:28px; }
        .sf-hero h1 { margin:0; font-family:'Fraunces',serif; font-size:clamp(2.2rem, 3vw, 2.8rem); line-height:1.02; letter-spacing:-0.03em; }
        .sf-panel { background:rgba(34,54,76,0.96); border:1px solid rgba(255,255,255,0.02); border-radius:18px; padding:24px; box-shadow:0 26px 60px -38px rgba(0,0,0,0.9); }
        .sf-form { display:flex; gap:12px; margin-bottom:24px; }
        .sf-input { flex:1; border-radius:12px; border:1px solid rgba(255,255,255,0.08); background:#0f1726; color:white; padding:12px 14px; }
        .sf-primary-btn { display:inline-flex; align-items:center; justify-content:center; border-radius:10px; padding:12px 18px; background:#B8001F; color:white; font-weight:600; border:none; }
        .sf-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; }
        .sf-card { background:#0f1726; border:1px solid rgba(255,255,255,0.06); border-radius:14px; overflow:hidden; }
        .sf-card img { width:100%; aspect-ratio:2/3; object-fit:cover; display:block; }
        @extends('admin.layout')

        @section('title', 'Import from TMDB')

        @section('content')
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Import from TMDB</h1>
            </div>

            <div class="bg-sf-surface border border-sf-border rounded-xl p-6">
                <form method="GET" action="{{ route('admin.films.search') }}" class="flex gap-3 mb-4">
                    <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search a movie title..." class="flex-1 rounded-lg border border-sf-border bg-sf-surface-light px-3 py-2">
                    <button class="px-4 py-2 rounded-lg bg-sf-blue text-white">Search</button>
                </form>

                <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                    @foreach ($results ?? [] as $movie)
                        <div class="bg-sf-surface border border-sf-border rounded-xl overflow-hidden">
                            @if ($movie['poster_url'])
                                <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="w-full h-64 object-cover">
                            @endif
                            <div class="p-3">
                                <div class="font-semibold text-sf-text">{{ $movie['title'] }}</div>
                                <div class="text-sm text-sf-muted">{{ $movie['release_year'] }}</div>
                                <form method="POST" action="{{ route('admin.films.import') }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="tmdb_id" value="{{ $movie['tmdb_id'] }}">
                                    <button class="w-full px-3 py-2 rounded-lg bg-sf-blue text-white">Import</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endsection
                            <p class="sf-card-year">{{ $movie['release_year'] }}</p>
