<x-filament-panels::page>
    @php
        $data = $this->getCalendarData();
        $price = $this->selectedVehicle?->price_per_day;
    @endphp

    <style>
        .rz-wrap{--rz-accent:#F97316;--rz-accent2:#fb923c;}
        .rz-card{background:#fff;border-radius:24px;box-shadow:0 1px 2px rgba(16,24,40,.04),0 12px 32px -12px rgba(16,24,40,.12);border:1px solid #f0f1f4;}
        .dark .rz-card{background:#18181b;border-color:#27272a;box-shadow:0 12px 32px -12px rgba(0,0,0,.5);}

        /* Sélecteur véhicule */
        .rz-select{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.1rem;}
        .rz-select svg{flex:none;color:var(--rz-accent);}
        .rz-select select{appearance:none;border:0;background:transparent;font-size:1.05rem;font-weight:600;color:#111827;width:100%;cursor:pointer;outline:none;}
        .dark .rz-select select{color:#fafafa;}
        .rz-select option{color:#111827;}

        /* En-tête mois */
        .rz-head{display:flex;align-items:center;justify-content:space-between;margin:1.25rem .25rem .25rem;}
        .rz-month{font-size:1.5rem;font-weight:800;letter-spacing:-.02em;color:#0f172a;}
        .dark .rz-month{color:#fff;}
        .rz-nav{height:44px;width:44px;display:flex;align-items:center;justify-content:center;border-radius:999px;background:#fff;border:1px solid #e9eaee;color:#475569;transition:.15s;cursor:pointer;}
        .rz-nav:hover{border-color:var(--rz-accent);color:var(--rz-accent);transform:translateY(-1px);box-shadow:0 6px 16px -8px rgba(249,115,22,.5);}
        .dark .rz-nav{background:#27272a;border-color:#3f3f46;color:#d4d4d8;}
        .rz-today{display:inline-flex;align-items:center;height:44px;padding:0 1.25rem;border-radius:999px;font-weight:700;font-size:.9rem;color:#fff;background:linear-gradient(135deg,var(--rz-accent2),var(--rz-accent));box-shadow:0 8px 20px -8px rgba(249,115,22,.7);cursor:pointer;transition:.15s;border:0;}
        .rz-today:hover{filter:brightness(1.05);transform:translateY(-1px);}
        .rz-hint{margin:.5rem .25rem 0;font-size:.85rem;color:#94a3b8;}

        /* Grille */
        .rz-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem;}
        .rz-dow{text-align:center;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;padding:.4rem 0 .7rem;}
        .rz-dow.we{color:#fb7185;}
        .rz-cell{position:relative;aspect-ratio:1/1;border-radius:18px;padding:.55rem .6rem;display:flex;flex-direction:column;justify-content:space-between;transition:transform .14s,box-shadow .14s,border-color .14s;}
        .rz-num{font-size:.95rem;font-weight:700;line-height:1;}
        .rz-price{font-size:.66rem;font-weight:600;opacity:.6;}
        .rz-check{position:absolute;bottom:.5rem;right:.55rem;width:16px;height:16px;opacity:.95;}

        /* États */
        .rz-available{background:#fff;border:1.5px solid #eceef2;color:#0f172a;cursor:pointer;}
        .rz-available:hover{border-color:var(--rz-accent);transform:translateY(-3px);box-shadow:0 14px 26px -14px rgba(249,115,22,.55);}
        .dark .rz-available{background:#1f1f23;border-color:#2e2e35;color:#f4f4f5;}
        .rz-reserved{background:linear-gradient(135deg,#34d399,#059669);color:#fff;box-shadow:0 10px 22px -12px rgba(5,150,105,.7);}
        .rz-blocked{background:linear-gradient(135deg,#fb7185,#e11d48);color:#fff;cursor:pointer;box-shadow:0 10px 22px -12px rgba(225,29,72,.7);}
        .rz-blocked:hover{transform:translateY(-3px);filter:brightness(1.03);}
        .rz-maintenance{background:linear-gradient(135deg,#fcd34d,#f59e0b);color:#fff;box-shadow:0 10px 22px -12px rgba(245,158,11,.7);}
        .rz-past{background:#f6f7f9;color:#cbd1da;}
        .dark .rz-past{background:#19191c;color:#52525b;}
        .rz-today-cell{box-shadow:0 0 0 2.5px var(--rz-accent), 0 14px 26px -14px rgba(249,115,22,.6);}
        .rz-num-today{display:inline-flex;align-items:center;justify-content:center;height:26px;min-width:26px;padding:0 4px;border-radius:999px;background:var(--rz-accent);color:#fff;}

        /* Légende */
        .rz-legend{display:flex;flex-wrap:wrap;gap:.5rem .65rem;}
        .rz-chip{display:inline-flex;align-items:center;gap:.45rem;font-size:.82rem;font-weight:600;color:#475569;background:#f7f8fa;border:1px solid #eceef2;padding:.35rem .7rem;border-radius:999px;}
        .dark .rz-chip{background:#27272a;border-color:#3f3f46;color:#d4d4d8;}
        .rz-dot{width:14px;height:14px;border-radius:5px;}
        .rz-dot.round{border-radius:999px;}

        /* Stats */
        .rz-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
        @media (max-width:640px){.rz-stats{grid-template-columns:1fr;}}
        .rz-stat{border-radius:20px;padding:1.4rem;text-align:center;border:1px solid #f0f1f4;background:#fff;}
        .dark .rz-stat{background:#18181b;border-color:#27272a;}
        .rz-stat .n{font-size:2rem;font-weight:800;line-height:1;letter-spacing:-.02em;}
        .rz-stat .l{margin-top:.45rem;font-size:.85rem;color:#94a3b8;font-weight:500;}
    </style>

    <div class="rz-wrap space-y-4">
        {{-- Sélecteur de véhicule --}}
        <div class="rz-card">
            <div class="rz-select">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6m0 0v-4.5m0 4.5H2.25m13.5-9V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18.75"/></svg>
                <select wire:model.live="vehicleId">
                    @forelse($this->vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }} — {{ number_format($v->price_per_day, 0, ',', ' ') }} DA/j</option>
                    @empty
                        <option>Aucun véhicule actif</option>
                    @endforelse
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>

        {{-- En-tête mois --}}
        <div class="rz-head">
            <button type="button" wire:click="previousMonth" class="rz-nav" aria-label="Mois précédent">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <div class="rz-month">{{ $data['monthLabel'] }}</div>
            <div style="display:flex;align-items:center;gap:.6rem;">
                <button type="button" wire:click="goToday" class="rz-today">Aujourd'hui</button>
                <button type="button" wire:click="nextMonth" class="rz-nav" aria-label="Mois suivant">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>
        </div>
        <p class="rz-hint">Cliquez sur un jour libre pour le bloquer, ou sur un jour bloqué pour le libérer.</p>

        {{-- Grille calendrier --}}
        <div class="rz-card" style="padding:1.25rem;">
            <div class="rz-grid">
                @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $i => $jour)
                    <div class="rz-dow {{ $i >= 5 ? 'we' : '' }}">{{ $jour }}</div>
                @endforeach
            </div>
            @foreach($data['weeks'] as $week)
                <div class="rz-grid" style="margin-top:.5rem;">
                    @foreach($week as $cell)
                        @if($cell === null)
                            <div></div>
                        @else
                            @php
                                $cls = match($cell['status']) {
                                    'reserved' => 'rz-reserved',
                                    'maintenance' => 'rz-maintenance',
                                    'blocked' => 'rz-blocked',
                                    'past' => 'rz-past',
                                    default => 'rz-available',
                                };
                            @endphp
                            <div
                                @if($cell['clickable']) wire:click="toggleDay('{{ $cell['date'] }}')" wire:loading.attr="disabled" @endif
                                class="rz-cell {{ $cls }} {{ $cell['isToday'] ? 'rz-today-cell' : '' }}">
                                <span class="rz-num {{ $cell['isToday'] ? 'rz-num-today' : '' }}">{{ $cell['day'] }}</span>
                                @if($cell['status'] === 'available' && $price)
                                    <span class="rz-price">{{ number_format($price, 0, ',', ' ') }} DA</span>
                                @elseif($cell['status'] === 'reserved')
                                    <svg class="rz-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Légende --}}
        <div class="rz-card" style="padding:1.1rem 1.25rem;">
            <div class="rz-legend">
                <span class="rz-chip"><span class="rz-dot" style="background:#fff;border:1.5px solid #d8dce3;"></span> Disponible</span>
                <span class="rz-chip"><span class="rz-dot" style="background:linear-gradient(135deg,#34d399,#059669);"></span> Réservé</span>
                <span class="rz-chip"><span class="rz-dot" style="background:linear-gradient(135deg,#fb7185,#e11d48);"></span> Bloqué par vous</span>
                <span class="rz-chip"><span class="rz-dot" style="background:linear-gradient(135deg,#fcd34d,#f59e0b);"></span> Maintenance</span>
                <span class="rz-chip"><span class="rz-dot round" style="background:#F97316;"></span> Aujourd'hui</span>
                <span class="rz-chip"><span class="rz-dot" style="background:#eef0f3;"></span> Passé</span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="rz-stats">
            <div class="rz-stat"><div class="n" style="color:#0f172a;">{{ $data['stats']['available'] }}</div><div class="l">Jours disponibles</div></div>
            <div class="rz-stat"><div class="n" style="color:#e11d48;">{{ $data['stats']['blocked'] }}</div><div class="l">Jours bloqués</div></div>
            <div class="rz-stat"><div class="n" style="color:#059669;">{{ $data['stats']['reserved'] }}</div><div class="l">Jours réservés</div></div>
        </div>
    </div>
</x-filament-panels::page>
