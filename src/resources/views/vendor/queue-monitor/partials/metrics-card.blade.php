@php
    $metricLangString = Str::lower(Str::snake($metric->title));
@endphp

<div class="p-6 border border-slate-800 rounded-2xl shadow-xl">

    <div class="text-sm font-semibold text-black-500"
        title="{{ __('Last :days days', ['days' => config('queue-monitor.ui.metrics_time_frame') ?? 14]) }}">
        {{ $metric->title }}
    </div>

    <div class="mt-3 text-3xl font-bold text-black-500">
        {{ $metric->format($metric->value) }}
    </div>

    @if ($metric->previousValue !== null)
        <div
            class="mt-3 text-sm font-semibold
            {{ $metric->hasChanged() ? ($metric->hasIncreased() ? 'text-emerald-400' : 'text-red-400') : 'text-slate-500' }}">

            @if ($metric->hasChanged())
                @if ($metric->hasIncreased())
                    Up from
                @else
                    Down from
                @endif
            @else
                No change from
            @endif

            {{ $metric->format($metric->previousValue) }}
        </div>
    @endif

</div>
