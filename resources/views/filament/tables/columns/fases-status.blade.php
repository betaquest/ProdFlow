<div class="flex flex-wrap gap-2">
    @foreach ($getRecord()->avances as $avance)
        @php
            $icon = match ($avance->estado) {
                'done' => '✅',
                'progress' => '⏳',
                default => '⬜',
            };
        @endphp
        <div class="flex items-center gap-1">
            <span>{{ $icon }}</span>
            <span class="text-sm text-gray-700 dark:text-gray-300">
                {{ $avance->fase->nombre }}
            </span>
        </div>
    @endforeach
</div>
