<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="p-6 bg-gray-900 rounded-2xl">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">🧠</span>
                <h2 class="text-2xl font-black text-white">Briefing du jour</h2>
            </div>
            <p class="text-gray-400">Votre résumé quotidien généré par l'IA — réservations, retours, chiffre d'affaires, conseils.</p>
        </div>

        {{-- Briefing content --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

            @if($briefingHtml)
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $briefingHtml !!}
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button wire:click="generateBriefing" wire:loading.attr="disabled" wire:target="generateBriefing"
                        style="background:#f97316;color:#fff;padding:10px 20px;font-weight:700;border-radius:12px;cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                        <span wire:loading.remove wire:target="generateBriefing">Actualiser le briefing</span>
                        <span wire:loading wire:target="generateBriefing">Actualisation...</span>
                    </button>
                    <p class="text-xs text-gray-500 self-center">Le briefing est mis en cache pour la journée. Cliquez pour forcer un nouveau.</p>
                </div>
            @else
                <div class="text-center py-12">
                    <span class="text-5xl mb-4 block">📊</span>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Votre briefing n'a pas encore été généré aujourd'hui</h3>
                    <p class="text-gray-500 mb-6">L'IA va analyser vos réservations, retours et chiffre d'affaires pour vous donner un résumé clair.</p>
                    <button wire:click="generateBriefing" wire:loading.attr="disabled" wire:target="generateBriefing"
                        style="background:#f97316;color:#fff;padding:14px 28px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;font-size:16px;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                        <span wire:loading.remove wire:target="generateBriefing">Générer mon briefing</span>
                        <span wire:loading wire:target="generateBriefing">Analyse en cours...</span>
                    </button>
                </div>
            @endif
        </div>

    </div>
</x-filament-panels::page>
