<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Calendrier de ce véhicule</x-slot>
        <x-slot name="description">Réservations (couleurs par statut) et blocages 🔧</x-slot>

        @php $calId = 'vehicle-cal-'.($this->record?->id ?? 'x'); @endphp
        <div id="{{ $calId }}" wire:ignore></div>

        <script>
            (function () {
                const elId = @js($calId);
                const events = @json($this->getEvents());

                function boot() {
                    const el = document.getElementById(elId);
                    if (!el || el.dataset.loaded) return;
                    el.dataset.loaded = '1';
                    const cal = new FullCalendar.Calendar(el, {
                        locale: 'fr',
                        initialView: 'dayGridMonth',
                        height: 'auto',
                        headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                        buttonText: { today: "Aujourd'hui" },
                        events: events,
                    });
                    cal.render();
                }

                if (window.FullCalendar) {
                    boot();
                } else {
                    const css = document.createElement('link');
                    // FullCalendar 6 inclut son CSS dans le JS global ; rien à charger séparément.
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js';
                    s.onload = function () {
                        const fr = document.createElement('script');
                        fr.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/fr.global.min.js';
                        fr.onload = boot;
                        document.head.appendChild(fr);
                    };
                    document.head.appendChild(s);
                }
            })();
        </script>
    </x-filament::section>
</x-filament-widgets::widget>
