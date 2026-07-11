@props(['mode', 'season'])

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ $mode === 'league' ? route('league.seasons.index') : route('cup.seasons.index') }}">
                {{ ucfirst($mode) }} Seasons
            </a>
        </li>
        <li class="breadcrumb-item active">Season {{ $season->season }}</li>
    </ol>
</nav>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs($mode.'.seasons.show') ? 'active' : '' }}"
           href="{{ route($mode.'.seasons.show', $season->id) }}">Standings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs($mode.'.matches.*') ? 'active' : '' }}"
           href="{{ route($mode.'.matches.index', $season->id) }}">Matches</a>
    </li>
    @if($mode === 'cup')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('cup.eliminate.*') ? 'active' : '' }}"
           href="{{ route('cup.eliminate.index', $season->id) }}">Knockout</a>
    </li>
    @endif
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs($mode.'.statistics.index') ? 'active' : '' }}"
           href="{{ route($mode.'.statistics.index', $season->id) }}">Statistics</a>
    </li>
</ul>
