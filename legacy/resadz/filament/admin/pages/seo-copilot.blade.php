<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="p-6 bg-gray-900 rounded-2xl">
            <div class="flex items-center gap-3 mb-2">
                <svg class="w-8 h-8 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <h2 class="text-2xl font-black text-white">SEO Copilot</h2>
            </div>
            <p class="text-gray-400">Assistant IA pour optimiser votre SEO — propulsé par Claude (Anthropic)</p>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-3">
            @foreach([
                'audit' => 'Audit SEO',
                'optimize' => 'Optimiser',
                'meta' => 'Meta Tags',
                'vehicle' => 'Descriptions véhicules',
                'ads' => 'Pubs véhicules',
                'social' => 'Réseaux sociaux',
                'faq' => 'Générer FAQ',
            ] as $tab => $label)
                <button wire:click="$set('activeTab', '{{ $tab }}')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $activeTab === $tab ? 'bg-violet-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ═══ TAB: AUDIT SEO ═══ --}}
        @if($activeTab === 'audit')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Auditer un article de blog</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Article</label>
                    <select wire:model="selectedPostId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        <option value="">Sélectionner un article...</option>
                        @foreach($this->getBlogPosts() as $post)
                            <option value="{{ $post->id }}">{{ $post->title }} {{ $post->is_published ? '✅' : '📝' }}</option>
                        @endforeach
                    </select>
                </div>
                <button wire:click="runAudit" wire:loading.attr="disabled" wire:target="runAudit"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="runAudit">Auditer cet article</span>
                    <span wire:loading wire:target="runAudit">Analyse en cours...</span>
                </button>
            </div>

            @if($auditResult)
            <div class="mt-6 space-y-4">
                {{-- Score --}}
                <div class="flex items-center gap-4 p-4 rounded-xl {{ ($auditResult['score'] ?? 0) >= 70 ? 'bg-green-50 dark:bg-green-900/20' : (($auditResult['score'] ?? 0) >= 40 ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-red-50 dark:bg-red-900/20') }}">
                    <div class="text-5xl font-black {{ ($auditResult['score'] ?? 0) >= 70 ? 'text-green-600' : (($auditResult['score'] ?? 0) >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ $auditResult['score'] ?? 0 }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">Score SEO / 100</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            @if(($auditResult['score'] ?? 0) >= 70) Bon score ! Quelques améliorations possibles.
                            @elseif(($auditResult['score'] ?? 0) >= 40) Score moyen — optimisations nécessaires.
                            @else Score faible — article à retravailler.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Issues --}}
                @if(!empty($auditResult['issues']))
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Problèmes détectés</h4>
                    <div class="space-y-2">
                        @foreach($auditResult['issues'] as $issue)
                            <div class="flex items-start gap-2 p-3 rounded-lg {{ ($issue['severity'] ?? '') === 'critical' ? 'bg-red-50 dark:bg-red-900/20' : (($issue['severity'] ?? '') === 'warning' ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }}">
                                @if(($issue['severity'] ?? '') === 'critical') <span class="text-red-500 font-bold text-xs px-2 py-0.5 bg-red-100 rounded-full">CRITIQUE</span>
                                @elseif(($issue['severity'] ?? '') === 'warning') <span class="text-amber-600 font-bold text-xs px-2 py-0.5 bg-amber-100 rounded-full">ATTENTION</span>
                                @else <span class="text-blue-600 font-bold text-xs px-2 py-0.5 bg-blue-100 rounded-full">INFO</span>
                                @endif
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $issue['message'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Suggestions --}}
                @if(!empty($auditResult['suggestions']))
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Suggestions d'amélioration</h4>
                    <ul class="space-y-1">
                        @foreach($auditResult['suggestions'] as $sug)
                            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="text-violet-500 mt-0.5">💡</span> {{ $sug }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Internal links --}}
                @if(!empty($auditResult['internal_links_suggestions']))
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2">Liens internes suggérés</h4>
                    <div class="space-y-2">
                        @foreach($auditResult['internal_links_suggestions'] as $link)
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-sm">
                                <code class="text-violet-600">&lt;a href="{{ $link['url'] ?? '' }}"&gt;{{ $link['anchor'] ?? '' }}&lt;/a&gt;</code>
                                <p class="text-gray-500 mt-1">{{ $link['reason'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Bouton corriger --}}
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="autoFixFromAudit" wire:loading.attr="disabled" wire:target="autoFixFromAudit"
                        style="background:#16a34a;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(22,163,74,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                        <span wire:loading.remove wire:target="autoFixFromAudit">Corriger automatiquement cet article</span>
                        <span wire:loading wire:target="autoFixFromAudit">Correction en cours...</span>
                    </button>
                    <p class="text-xs text-gray-500 mt-2">L'IA va réécrire l'article en appliquant toutes les suggestions ci-dessus.</p>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: OPTIMIZE ═══ --}}
        @if($activeTab === 'optimize')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Optimiser un article en 1 clic</h3>
            <div class="space-y-3">
                <select wire:model="optimizePostId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Sélectionner un article...</option>
                    @foreach($this->getBlogPosts() as $post)
                        <option value="{{ $post->id }}">{{ $post->title }}</option>
                    @endforeach
                </select>
                <button wire:click="runOptimize" wire:loading.attr="disabled" wire:target="runOptimize"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="runOptimize">Optimiser avec l'IA</span>
                    <span wire:loading wire:target="runOptimize">Optimisation en cours...</span>
                </button>
            </div>

            @if($optimizeResult)
            <div class="mt-6 space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl">
                    <p class="font-bold text-green-900 dark:text-green-100">Nouveau titre : {{ $optimizeResult['title'] ?? '' }}</p>
                    <p class="text-sm text-green-800 dark:text-green-200 mt-1">Meta : {{ $optimizeResult['meta_description'] ?? '' }}</p>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl max-h-96 overflow-y-auto">
                    <div class="prose prose-sm dark:prose-invert max-w-none">{!! $optimizeResult['content'] ?? '' !!}</div>
                </div>
                <button wire:click="applyOptimization"
                    style="background:#16a34a;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;cursor:pointer;border:none;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    Appliquer cette version
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: META TAGS ═══ --}}
        @if($activeTab === 'meta')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Générer Meta Tags SEO</h3>
            <div class="space-y-3">
                <select wire:model="metaPostId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Sélectionner un article...</option>
                    @foreach($this->getBlogPosts() as $post)
                        <option value="{{ $post->id }}">{{ $post->title }}</option>
                    @endforeach
                </select>
                <button wire:click="generateMeta" wire:loading.attr="disabled" wire:target="generateMeta"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="generateMeta">Générer</span>
                    <span wire:loading wire:target="generateMeta">Génération...</span>
                </button>
            </div>

            @if($metaResult)
            <div class="mt-6 space-y-3">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">Meta Title ({{ strlen($metaResult['meta_title'] ?? '') }} chars)</p>
                    <p class="text-blue-800 dark:text-blue-200 font-bold">{{ $metaResult['meta_title'] ?? '' }}</p>
                </div>
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">Meta Description ({{ strlen($metaResult['meta_description'] ?? '') }} chars)</p>
                    <p class="text-blue-800 dark:text-blue-200">{{ $metaResult['meta_description'] ?? '' }}</p>
                </div>
                <button wire:click="applyMeta"
                    style="background:#16a34a;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;cursor:pointer;border:none;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    Appliquer
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: VEHICLE DESCRIPTIONS ═══ --}}
        @if($activeTab === 'vehicle')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Enrichir les descriptions véhicules</h3>
            <div class="space-y-3">
                <select wire:model="selectedVehicleId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Sélectionner un véhicule...</option>
                    @foreach($this->getVehicles() as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }} — {{ number_format($v->price_per_day, 0, ',', ' ') }} DA/j {{ $v->description ? '✅' : '❌' }}</option>
                    @endforeach
                </select>
                <button wire:click="generateVehicleDesc" wire:loading.attr="disabled" wire:target="generateVehicleDesc"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="generateVehicleDesc">Générer description</span>
                    <span wire:loading wire:target="generateVehicleDesc">Génération...</span>
                </button>
            </div>

            @if($vehicleDescription)
            <div class="mt-6 space-y-4">
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div class="prose prose-sm dark:prose-invert max-w-none">{!! $vehicleDescription !!}</div>
                </div>
                <button wire:click="applyVehicleDesc"
                    style="background:#16a34a;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;cursor:pointer;border:none;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    Enregistrer cette description
                </button>
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: VEHICLE ADS ═══ --}}
        @if($activeTab === 'ads')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Générer des pubs Facebook/Instagram par véhicule</h3>
            <div class="space-y-3">
                <select wire:model="adVehicleId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Sélectionner un véhicule...</option>
                    @foreach($this->getVehicles() as $v)
                        <option value="{{ $v->id }}">{{ $v->full_name }} — {{ number_format($v->price_per_day, 0, ',', ' ') }} DA/j ({{ $v->loueur->wilaya ?? '?' }})</option>
                    @endforeach
                </select>
                <button wire:click="generateVehicleAd" wire:loading.attr="disabled" wire:target="generateVehicleAd"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="generateVehicleAd">Générer les pubs</span>
                    <span wire:loading wire:target="generateVehicleAd">Génération en cours...</span>
                </button>
            </div>

            @if($adResult)
            <div class="mt-6 space-y-4">

                {{-- Facebook Ad --}}
                @if(!empty($adResult['facebook_ad']))
                <div class="p-5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span class="font-bold text-blue-900 dark:text-blue-100">Facebook Ad (sponsorisée)</span>
                    </div>
                    <div class="space-y-2">
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 mb-1">TITRE</p>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $adResult['facebook_ad']['headline'] ?? '' }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 mb-1">TEXTE PRINCIPAL</p>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $adResult['facebook_ad']['primary_text'] ?? '' }}</p>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
                            <p class="text-xs font-semibold text-gray-500 mb-1">DESCRIPTION</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $adResult['facebook_ad']['description'] ?? '' }}</p>
                        </div>
                    </div>
                    <button onclick="navigator.clipboard.writeText(@js(($adResult['facebook_ad']['primary_text'] ?? '') . '\n\n' . ($adResult['facebook_ad']['headline'] ?? '')))" class="mt-3 text-xs text-blue-600 hover:underline">Copier tout</button>
                </div>
                @endif

                {{-- Instagram Ad --}}
                @if(!empty($adResult['instagram_ad']))
                <div class="p-5 bg-pink-50 dark:bg-pink-900/20 border border-pink-200 dark:border-pink-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-pink-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        <span class="font-bold text-pink-900 dark:text-pink-100">Instagram Ad</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $adResult['instagram_ad']['caption'] ?? '' }}</p>
                    <button onclick="navigator.clipboard.writeText(@js($adResult['instagram_ad']['caption'] ?? ''))" class="mt-3 text-xs text-pink-600 hover:underline">Copier</button>
                </div>
                @endif

                {{-- Facebook Organic --}}
                @if(!empty($adResult['facebook_organic']))
                <div class="p-5 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span class="font-bold text-indigo-900 dark:text-indigo-100">Post Facebook (organique)</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $adResult['facebook_organic']['post'] ?? '' }}</p>
                    <button onclick="navigator.clipboard.writeText(@js($adResult['facebook_organic']['post'] ?? ''))" class="mt-3 text-xs text-indigo-600 hover:underline">Copier</button>
                </div>
                @endif

                {{-- Story --}}
                @if(!empty($adResult['story_text']))
                <div class="p-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">📱</span>
                        <span class="font-bold text-amber-900 dark:text-amber-100">Story Instagram/Facebook</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white whitespace-pre-line">{{ $adResult['story_text'] }}</p>
                    <button onclick="navigator.clipboard.writeText(@js($adResult['story_text'] ?? ''))" class="mt-3 text-xs text-amber-600 hover:underline">Copier</button>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: SOCIAL MEDIA ═══ --}}
        @if($activeTab === 'social')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Générer des posts réseaux sociaux</h3>
            <div class="space-y-3">
                <select wire:model="socialPostId" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">Sélectionner un article...</option>
                    @foreach($this->getBlogPosts() as $post)
                        <option value="{{ $post->id }}">{{ $post->title }}</option>
                    @endforeach
                </select>
                <button wire:click="generateSocial" wire:loading.attr="disabled" wire:target="generateSocial"
                    style="background:#f97316;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;box-shadow:0 4px 6px rgba(249,115,22,0.3);cursor:pointer;border:none;" onmouseover="this.style.background='#ea580c'" onmouseout="this.style.background='#f97316'">
                    <span wire:loading.remove wire:target="generateSocial">Générer posts</span>
                    <span wire:loading wire:target="generateSocial">Génération...</span>
                </button>
            </div>

            @if($socialResult)
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        <span class="font-bold text-blue-900 dark:text-blue-100">Facebook</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $socialResult['facebook'] ?? '' }}</p>
                    <button onclick="navigator.clipboard.writeText(@js($socialResult['facebook'] ?? ''))" class="mt-3 text-xs text-blue-600 hover:underline">Copier</button>
                </div>
                <div class="p-4 bg-pink-50 dark:bg-pink-900/20 border border-pink-200 dark:border-pink-700 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-pink-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        <span class="font-bold text-pink-900 dark:text-pink-100">Instagram</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $socialResult['instagram'] ?? '' }}</p>
                    <button onclick="navigator.clipboard.writeText(@js($socialResult['instagram'] ?? ''))" class="mt-3 text-xs text-pink-600 hover:underline">Copier</button>
                </div>
            </div>

            <div class="mt-4">
                <button wire:click="saveSocialPosts"
                    style="background:#16a34a;color:#fff;padding:12px 24px;font-weight:700;border-radius:12px;cursor:pointer;border:none;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    Sauvegarder dans l'article
                </button>
                <p class="text-xs text-gray-500 mt-2">Les posts seront enregistrés dans les champs de l'article (badge_text = Facebook, badge_value = Instagram).</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ TAB: FAQ ═══ --}}
        @if($activeTab === 'faq')
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Générer une FAQ SEO</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sujet</label>
                    <input type="text" wire:model="faqTopic" placeholder="Ex: Location voiture Alger"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contexte (optionnel)</label>
                    <input type="text" wire:model="faqContext" placeholder="Ex: page wilaya, catégorie SUV..."
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                </div>
            </div>
            <button wire:click="generateFaq" wire:loading.attr="disabled" wire:target="generateFaq"
                class="mt-4 px-6 py-3 bg-orange-500 text-white font-bold rounded-xl hover:bg-orange-600 transition disabled:opacity-50 shadow-md">
                <span wire:loading.remove wire:target="generateFaq">Générer FAQ</span>
                <span wire:loading wire:target="generateFaq">Génération...</span>
            </button>

            @if($faqResult && !empty($faqResult['questions']))
            <div class="mt-6 space-y-3">
                @foreach($faqResult['questions'] as $faq)
                    <div class="p-4 bg-teal-50 dark:bg-teal-900/20 rounded-xl">
                        <p class="font-bold text-teal-900 dark:text-teal-100">{{ $faq['question'] ?? '' }}</p>
                        <p class="text-sm text-teal-800 dark:text-teal-200 mt-1">{{ $faq['answer'] ?? '' }}</p>
                    </div>
                @endforeach

                {{-- Schema.org code to copy --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <p class="font-bold text-gray-900 dark:text-white mb-2">Code Schema.org (copiez-collez dans votre page) :</p>
                    <pre class="text-xs overflow-x-auto bg-gray-900 text-green-400 p-3 rounded-lg"><code>&lt;script type="application/ld+json"&gt;
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
@foreach($faqResult['questions'] as $faq)
        {
            "@@type": "Question",
            "name": "{{ $faq['question'] ?? '' }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ $faq['answer'] ?? '' }}"
            }
        }{{ !$loop->last ? ',' : '' }}
@endforeach
    ]
}
&lt;/script&gt;</code></pre>
                </div>
            </div>
            @endif
        </div>
        @endif

    </div>
</x-filament-panels::page>
