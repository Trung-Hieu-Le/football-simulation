@props([
    'title',
    'matches',
    'simulateUrl' => null,
    'simulateLabel' => 'Simulate Round',
    'matchShowRoute' => null,
    'knockout' => false,
])

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $title }}</span>
        @if($simulateUrl)
            <form action="{{ $simulateUrl }}" method="POST">@csrf
                <button class="btn btn-sm {{ $knockout ? 'btn-warning' : 'btn-success' }}">{{ $simulateLabel }}</button>
            </form>
        @endif
    </div>
    <div class="card-body">
        @foreach($matches as $match)
            <x-match-row
                :match="$match"
                :show-url="$matchShowRoute ? route($matchShowRoute, $match->id) : null"
                :knockout="$knockout"
            />
        @endforeach
    </div>
</div>
