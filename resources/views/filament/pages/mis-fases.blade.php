<x-filament-panels::page>
    <x-filament::tabs>
        @foreach ($this->getTabs() as $tabKey => $tab)
            @php
                $isActive = $this->activeTab === $tabKey;
            @endphp

            <x-filament::tabs.item
                :active="$isActive"
                :badge="$tab->getBadge()"
                :badge-color="$tab->getBadgeColor()"
                :icon="$tab->getIcon()"
                wire:click="$set('activeTab', '{{ $tabKey }}')"
            >
                {{ $tab->getLabel() }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{-- Tabla de fases --}}
    {{ $this->table }}
</x-filament-panels::page>
