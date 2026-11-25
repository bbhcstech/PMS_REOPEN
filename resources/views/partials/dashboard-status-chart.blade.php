@php
    $total = array_sum($counts);
    $palette = ['#4f83ff', '#22c69a', '#f5b94c', '#a78bfa', '#f4728b', '#22d3ee', '#fb923c'];
    $segments = [];
    $offset = 0;
    foreach (array_values($counts) as $index => $count) {
        $end = $offset + ($total ? $count / $total * 100 : 0);
        $segments[] = $palette[$index % count($palette)] . ' ' . $offset . '% ' . $end . '%';
        $offset = $end;
    }
@endphp
<div class="dashboard-status-chart">
    @if($total)
        <div class="dashboard-status-ring" style="background: conic-gradient({{ implode(', ', $segments) }});" role="img" aria-label="{{ $total }} {{ $subject }} by status; counts listed below">
            <div class="dashboard-status-center"><strong>{{ number_format($total) }}</strong><span>Total {{ $subject }}</span></div>
        </div>
        <ul class="dashboard-status-legend" aria-label="{{ ucfirst($subject) }} status breakdown">
            @foreach($counts as $label => $count)
                <li><span class="dashboard-status-dot" style="background: {{ $palette[$loop->index % count($palette)] }};"></span><span>{{ $label }}</span><strong>{{ number_format($count) }}</strong><small>{{ round($count / $total * 100, 1) }}%</small></li>
            @endforeach
        </ul>
    @else
        <div class="dashboard-status-empty"><strong>No {{ $subject }} yet</strong><span>Your {{ $subject }} will appear here when available.</span></div>
    @endif
</div>
