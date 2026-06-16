<x-filament-panels::page>
    @php $data = $this->getCalendarData(); @endphp

    {{-- Sélecteur de véhicule --}}
    <div class="fi-section rounded-2xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center gap-3">
            <x-filament::icon icon="heroicon-o-truck" class="h-6 w-6 text-gray-400 shrink-0" />
            <select wire:model.live="vehicleId"
                    class="w-full border-0 bg-transparent text-base font-medium text-gray-900 focus:ring-0 dark:text-white">
                @forelse($this->vehicles as $v)
                    <option value="{{ $v->id }}">{{ $v->full_name }} — {{ number_format($v->price_per_day, 0, ',', ' ') }} DA/j</option>
                @empty
                    <option>Aucun véhicule actif</option>
                @endforelse
            </select>
        </div>
    </div>

    {{-- En-tête mois + navigation --}}
    <div class="mt-4 flex items-center justify-between">
        <button type="button" wire:click="previousMonth"
                class="flex h-10 w-10 items-center justify-center rounded-full ring-1 ring-gray-200 hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
            <x-filament::icon icon="heroicon-m-chevron-left" class="h-5 w-5 text-gray-500" />
        </button>

        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $data['monthLabel'] }}</h2>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="goToday"
                    class="rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                Aujourd'hui
            </button>
            <button type="button" wire:click="nextMonth"
                    class="flex h-10 w-10 items-center justify-center rounded-full ring-1 ring-gray-200 hover:bg-gray-50 dark:ring-white/10 dark:hover:bg-white/5">
                <x-filament::icon icon="heroicon-m-chevron-right" class="h-5 w-5 text-gray-500" />
            </button>
        </div>
    </div>

    <p class="mt-2 text-sm text-gray-400">Cliquez directement sur un jour pour le bloquer / débloquer.</p>

    {{-- Grille calendrier --}}
    <div class="mt-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid grid-cols-7 gap-2 text-center text-sm font-semibold">
            @foreach(['L','M','M','J','V','S','D'] as $i => $jour)
                <div class="py-2 {{ $i >= 5 ? 'text-rose-500' : 'text-gray-500' }}">{{ $jour }}</div>
            @endforeach
        </div>

        @foreach($data['weeks'] as $week)
            <div class="grid grid-cols-7 gap-2">
                @foreach($week as $cell)
                    @if($cell === null)
                        <div></div>
                    @else
                        @php
                            $bg = match($cell['status']) {
                                'reserved'    => 'bg-emerald-600 text-white ring-emerald-600',
                                'maintenance' => 'bg-amber-400 text-white ring-amber-400',
                                'blocked'     => 'bg-rose-500 text-white ring-rose-500',
                                'past'        => 'bg-gray-50 text-gray-300 ring-gray-100 dark:bg-white/5',
                                default       => 'bg-white text-gray-800 ring-gray-200 dark:bg-gray-800 dark:text-gray-100',
                            };
                        @endphp
                        <div
                            @if($cell['clickable']) wire:click="toggleDay('{{ $cell['date'] }}')" wire:loading.attr="disabled" @endif
                            class="relative flex aspect-square flex-col rounded-2xl p-2 ring-1 {{ $bg }} {{ $cell['clickable'] ? 'cursor-pointer transition hover:opacity-90' : '' }}"
                            title="{{ $cell['date'] }}">
                            <span class="text-sm font-semibold {{ $cell['isToday'] ? 'flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-white' : '' }}">
                                {{ $cell['day'] }}
                            </span>
                            @if($cell['status'] === 'reserved')
                                <x-filament::icon icon="heroicon-m-check" class="absolute bottom-1.5 right-1.5 h-4 w-4 text-white/90" />
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Légende --}}
    <div class="mt-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="mb-3 text-sm font-semibold text-gray-500">Légende</div>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded ring-1 ring-gray-300 bg-white"></span> Disponible</span>
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-rose-500"></span> Bloqué par vous</span>
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-emerald-600"></span> Réservé</span>
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded-full bg-amber-500"></span> Aujourd'hui</span>
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-gray-100"></span> Passé</span>
            <span class="flex items-center gap-2"><span class="h-4 w-4 rounded bg-amber-400"></span> Maintenance</span>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-5 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $data['stats']['available'] }}</div>
            <div class="text-sm text-gray-500">Jours disponibles</div>
        </div>
        <div class="rounded-2xl bg-white p-5 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-3xl font-extrabold text-rose-500">{{ $data['stats']['blocked'] }}</div>
            <div class="text-sm text-gray-500">Jours bloqués</div>
        </div>
        <div class="rounded-2xl bg-white p-5 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="text-3xl font-extrabold text-emerald-600">{{ $data['stats']['reserved'] }}</div>
            <div class="text-sm text-gray-500">Jours réservés</div>
        </div>
    </div>
</x-filament-panels::page>
