<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Tabs --}}
        <div style="display: flex; gap: 8px; background: #f1f5f9; padding: 6px; border-radius: 14px; width: fit-content;">
            <button wire:click="$set('activeTab', 'templates')"
                style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s;
                {{ $activeTab === 'templates' ? 'background: linear-gradient(135deg, #FF6B2C, #F59E0B); color: white; box-shadow: 0 4px 12px rgba(255,107,44,0.3);' : 'background: transparent; color: #64748b;' }}">
                Modèles prédéfinis
            </button>
            <button wire:click="$set('activeTab', 'ai')"
                style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s;
                {{ $activeTab === 'ai' ? 'background: linear-gradient(135deg, #6366F1, #8B5CF6); color: white; box-shadow: 0 4px 12px rgba(99,102,241,0.3);' : 'background: transparent; color: #64748b;' }}">
                Écrire avec l'IA
            </button>
            <button wire:click="$set('activeTab', 'history')"
                style="padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s;
                {{ $activeTab === 'history' ? 'background: #1e293b; color: white;' : 'background: transparent; color: #64748b;' }}">
                Historique ({{ $recentEmails->count() }})
            </button>
        </div>

        {{-- TAB 1: Templates --}}
        @if($activeTab === 'templates')
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Template list --}}
            <div class="lg:col-span-2 space-y-3">
                <h3 style="font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">Modèle d'email</h3>
                @php
                    $icons = [
                        'relance_paiement' => '💰',
                        'loueur_inactif' => '😴',
                        'avertissement_suppression' => '⚠️',
                        'bienvenue_personnalise' => '👋',
                        'rappel_calendrier' => '📅',
                        'demande_documents' => '📄',
                        'felicitations_reservation' => '🎉',
                        'promotion' => '📢',
                        'reset_password' => '🔑',
                    ];
                @endphp
                @foreach($templates as $key => $template)
                    <div wire:click="$set('selectedTemplate', '{{ $key }}')"
                         style="padding: 14px 16px; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px;
                         {{ $selectedTemplate === $key ? 'background: linear-gradient(135deg, #FFF7ED, #FEF3C7); border: 2px solid #F59E0B; box-shadow: 0 2px 8px rgba(245,158,11,0.15);' : 'background: white; border: 1px solid #e2e8f0;' }}">
                        <span style="font-size: 22px;">{{ $icons[$key] ?? '📧' }}</span>
                        <span style="font-weight: 600; font-size: 14px; color: #1e293b;">{{ $template['label'] }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Preview + Recipient + Send --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- Recipient --}}
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <h3 style="font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Envoyer via</h3>
                    <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <button wire:click="$set('sendMethod', 'email')"
                            style="padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px;
                            {{ $sendMethod === 'email' ? 'background: #1e293b; color: white;' : 'background: #f1f5f9; color: #64748b;' }}">
                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Email
                        </button>
                        <button wire:click="$set('sendMethod', 'whatsapp')"
                            style="padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px;
                            {{ $sendMethod === 'whatsapp' ? 'background: #25D366; color: white;' : 'background: #f1f5f9; color: #64748b;' }}">
                            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.644-1.217A11.95 11.95 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-2.115 0-4.093-.652-5.74-1.763l-.412-.247-2.756.723.735-2.686-.271-.432A9.71 9.71 0 012.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                            WhatsApp
                        </button>
                    </div>

                    <h3 style="font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Destinataire</h3>
                    <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                        <button wire:click="$set('recipientType', 'selected_loueurs')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $recipientType === 'selected_loueurs' ? 'background: #1e293b; color: white;' : 'background: #f1f5f9; color: #64748b;' }}">
                            Choisir des loueurs
                        </button>
                        <button wire:click="$set('recipientType', 'all_loueurs')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $recipientType === 'all_loueurs' ? 'background: #dc2626; color: white;' : 'background: #f1f5f9; color: #64748b;' }}">
                            Tous les loueurs
                        </button>
                        <button wire:click="$set('recipientType', 'custom')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $recipientType === 'custom' ? 'background: #1e293b; color: white;' : 'background: #f1f5f9; color: #64748b;' }}">
                            Email libre
                        </button>
                    </div>
                    @if($recipientType === 'all_loueurs')
                        <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; font-size: 13px; color: #991b1b;">
                            L'email sera envoyé à <strong>tous les loueurs actifs</strong> ({{ $loueurs->count() }}). Le {nom} sera remplacé automatiquement.
                        </div>
                    @elseif($recipientType === 'selected_loueurs')
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #d1d5db; border-radius: 10px; padding: 8px;">
                            @foreach($loueurs as $l)
                                <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: 14px; transition: background 0.15s;"
                                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                    <input type="checkbox" wire:model="selectedLoueurIds" value="{{ $l->id }}"
                                        style="width: 18px; height: 18px; accent-color: #16a34a; cursor: pointer;">
                                    <span style="font-weight: 500; color: #1e293b;">{{ $l->company_name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if(count($selectedLoueurIds) > 0)
                            <div style="margin-top: 8px; font-size: 13px; color: #16a34a; font-weight: 600;">
                                {{ count($selectedLoueurIds) }} loueur(s) sélectionné(s)
                            </div>
                        @endif
                    @elseif($recipientType === 'custom')
                        <input type="email" wire:model="customEmail" placeholder="email@exemple.com" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #d1d5db; font-size: 14px;" />
                    @endif
                </div>

                {{-- Email preview --}}
                @if($previewSubject)
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
                        {{-- Email header --}}
                        <div style="background: linear-gradient(135deg, #1e293b, #334155); padding: 16px 20px; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div style="flex: 1;">
                                <p style="color: white; font-weight: 700; font-size: 14px; margin: 0;">Aperçu de l'email</p>
                                <p style="color: rgba(255,255,255,0.6); font-size: 12px; margin: 2px 0 0 0;">Modifiable avant envoi</p>
                            </div>
                        </div>

                        <div style="padding: 20px;">
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Sujet</label>
                                <input type="text" wire:model="previewSubject" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 15px; font-weight: 600; margin-top: 4px;" />
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Corps du message</label>
                                <textarea wire:model="previewBody" rows="14" style="width: 100%; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 14px; line-height: 1.6; margin-top: 4px; resize: vertical;"></textarea>
                            </div>
                        </div>

                        <div style="padding: 0 20px 20px;">
                            <button wire:click="sendTemplate"
                                style="width: 100%; padding: 14px; color: white; font-weight: 700; font-size: 15px; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
                                {{ $sendMethod === 'whatsapp' ? 'background: #25D366; box-shadow: 0 4px 14px rgba(37,211,102,0.3);' : 'background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 4px 14px rgba(34,197,94,0.3);' }}">
                                @if($sendMethod === 'whatsapp')
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                    Envoyer via WhatsApp
                                @else
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                    Envoyer l'email
                                @endif
                            </button>
                        </div>

                        {{-- WhatsApp Links --}}
                        @if(!empty($whatsappLinks))
                        <div style="padding: 0 20px 20px;">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px;">
                                <p style="font-weight: 700; color: #166534; font-size: 14px; margin-bottom: 12px;">Cliquez pour envoyer à chaque loueur :</p>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach($whatsappLinks as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                            style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #25D366; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                            {{ $link['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 60px 20px; text-align: center;">
                        <svg width="64" height="64" fill="none" stroke="#d1d5db" viewBox="0 0 24 24" stroke-width="1" style="margin: 0 auto 16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <p style="color: #94a3b8; font-size: 15px;">Sélectionnez un modèle à gauche</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- TAB 2: AI --}}
        @if($activeTab === 'ai')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- AI Input --}}
            <div style="background: linear-gradient(135deg, #EEF2FF, #F5F3FF); border: 2px solid #C7D2FE; border-radius: 20px; padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366F1, #8B5CF6); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <h3 style="font-weight: 800; color: #312E81; font-size: 18px; margin: 0;">Rédaction IA</h3>
                        <p style="color: #6366F1; font-size: 13px; margin: 2px 0 0 0;">Décrivez, l'IA rédige</p>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #4338CA; text-transform: uppercase;">Envoyer via</label>
                    <div style="display: flex; gap: 8px; margin-top: 6px; margin-bottom: 12px;">
                        <button wire:click="$set('aiSendMethod', 'email')"
                            style="padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px;
                            {{ $aiSendMethod === 'email' ? 'background: #4338CA; color: white;' : 'background: white; color: #6366F1; border: 1px solid #C7D2FE;' }}">
                            Email
                        </button>
                        <button wire:click="$set('aiSendMethod', 'whatsapp')"
                            style="padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; border: none; display: flex; align-items: center; gap: 6px;
                            {{ $aiSendMethod === 'whatsapp' ? 'background: #25D366; color: white;' : 'background: white; color: #6366F1; border: 1px solid #C7D2FE;' }}">
                            WhatsApp
                        </button>
                    </div>
                    <label style="font-size: 12px; font-weight: 700; color: #4338CA; text-transform: uppercase;">Destinataire</label>
                    <div style="display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                        <button wire:click="$set('aiRecipientType', 'selected_loueurs')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $aiRecipientType === 'selected_loueurs' ? 'background: #4338CA; color: white;' : 'background: white; color: #6366F1; border: 1px solid #C7D2FE;' }}">
                            Choisir des loueurs
                        </button>
                        <button wire:click="$set('aiRecipientType', 'all_loueurs')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $aiRecipientType === 'all_loueurs' ? 'background: #dc2626; color: white;' : 'background: white; color: #6366F1; border: 1px solid #C7D2FE;' }}">
                            Tous les loueurs
                        </button>
                        <button wire:click="$set('aiRecipientType', 'custom')"
                            style="padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;
                            {{ $aiRecipientType === 'custom' ? 'background: #4338CA; color: white;' : 'background: white; color: #6366F1; border: 1px solid #C7D2FE;' }}">
                            Email libre
                        </button>
                    </div>
                </div>

                @if($aiRecipientType === 'all_loueurs')
                    <div style="padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; font-size: 13px; color: #991b1b; margin-bottom: 16px;">
                        L'email sera envoyé à <strong>tous les loueurs actifs</strong> ({{ $loueurs->count() }}). Le {nom} sera remplacé automatiquement.
                    </div>
                @elseif($aiRecipientType === 'selected_loueurs')
                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #C7D2FE; border-radius: 10px; padding: 8px; margin-bottom: 16px;">
                        @foreach($loueurs as $l)
                            <label style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; cursor: pointer; font-size: 14px; transition: background 0.15s;"
                                onmouseover="this.style.background='#EEF2FF'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" wire:model="aiSelectedLoueurIds" value="{{ $l->id }}"
                                    style="width: 18px; height: 18px; accent-color: #4338CA; cursor: pointer;">
                                <span style="font-weight: 500; color: #1e293b;">{{ $l->company_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(count($aiSelectedLoueurIds) > 0)
                        <div style="margin-bottom: 16px; font-size: 13px; color: #4338CA; font-weight: 600;">
                            {{ count($aiSelectedLoueurIds) }} loueur(s) sélectionné(s)
                        </div>
                    @endif
                @elseif($aiRecipientType === 'loueur')
                    <select wire:model="aiSelectedLoueurId" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #C7D2FE; font-size: 14px; margin-bottom: 16px; background: white;">
                        <option value="">— Choisir un loueur —</option>
                        @foreach($loueurs as $l)
                            <option value="{{ $l->id }}">{{ $l->company_name }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="email" wire:model="aiCustomEmail" placeholder="email@exemple.com" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #C7D2FE; font-size: 14px; margin-bottom: 16px; background: white;" />
                @endif

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 12px; font-weight: 700; color: #4338CA; text-transform: uppercase;">Que voulez-vous lui dire ?</label>
                    <textarea wire:model="aiPrompt" rows="4" placeholder="Ex: Dis-lui qu'on a ajouté le paiement par CB et qu'il devrait configurer Stripe..."
                        style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #C7D2FE; font-size: 14px; margin-top: 6px; background: white; resize: vertical;"></textarea>
                </div>

                <button wire:click="generateWithAI" wire:loading.attr="disabled"
                    style="width: 100%; padding: 14px; background: linear-gradient(135deg, #6366F1, #8B5CF6); color: white; font-weight: 700; font-size: 15px; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(99,102,241,0.3);">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" wire:loading.class="animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span wire:loading.remove>Générer avec l'IA</span>
                    <span wire:loading>Génération...</span>
                </button>
            </div>

            {{-- AI Result --}}
            <div>
                @if($aiSubject || $aiBody)
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
                        <div style="background: linear-gradient(135deg, #6366F1, #8B5CF6); padding: 16px 20px; display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" fill="none" stroke="white" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <p style="color: white; font-weight: 700; font-size: 14px; margin: 0;">Email généré par l'IA — modifiable</p>
                        </div>
                        <div style="padding: 20px;">
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Sujet</label>
                                <input type="text" wire:model="aiSubject" style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 15px; font-weight: 600; margin-top: 4px;" />
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Corps</label>
                                <textarea wire:model="aiBody" rows="14" style="width: 100%; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 14px; line-height: 1.6; margin-top: 4px; resize: vertical;"></textarea>
                            </div>
                        </div>
                        <div style="padding: 0 20px 20px;">
                            <button wire:click="sendAIEmail"
                                style="width: 100%; padding: 14px; color: white; font-weight: 700; font-size: 15px; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
                                {{ $aiSendMethod === 'whatsapp' ? 'background: #25D366;' : 'background: linear-gradient(135deg, #22c55e, #16a34a);' }}">
                                {{ $aiSendMethod === 'whatsapp' ? 'Envoyer via WhatsApp' : 'Envoyer l\'email' }}
                            </button>
                        </div>

                        @if(!empty($aiWhatsappLinks))
                        <div style="padding: 0 20px 20px;">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px;">
                                <p style="font-weight: 700; color: #166534; font-size: 14px; margin-bottom: 12px;">Cliquez pour envoyer à chaque loueur :</p>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    @foreach($aiWhatsappLinks as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener"
                                            style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #25D366; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                            {{ $link['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 80px 20px; text-align: center;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #EEF2FF, #E0E7FF); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <svg width="40" height="40" fill="none" stroke="#6366F1" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <p style="color: #6366F1; font-weight: 600; font-size: 16px; margin: 0 0 4px 0;">L'IA est prête</p>
                        <p style="color: #94a3b8; font-size: 14px;">Décrivez ce que vous voulez dire et l'IA rédigera l'email</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- TAB 3: History --}}
        @if($activeTab === 'history')
        <div style="background: white; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="text-align: left; padding: 14px 16px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Date</th>
                            <th style="text-align: left; padding: 14px 16px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Destinataire</th>
                            <th style="text-align: left; padding: 14px 16px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Sujet</th>
                            <th style="text-align: left; padding: 14px 16px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Type</th>
                            <th style="text-align: left; padding: 14px 16px; font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEmails as $email)
                            <tr style="border-top: 1px solid #f1f5f9;">
                                <td style="padding: 12px 16px; color: #64748b; white-space: nowrap;">{{ $email->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 600; color: #1e293b;">{{ $email->to_name ?? '-' }}</div>
                                    <div style="font-size: 12px; color: #94a3b8;">{{ $email->to_email }}</div>
                                </td>
                                <td style="padding: 12px 16px; color: #475569; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $email->subject }}</td>
                                <td style="padding: 12px 16px;">
                                    @if($email->template_key === 'ai_generated')
                                        <span style="padding: 4px 10px; background: linear-gradient(135deg, #EEF2FF, #E0E7FF); color: #4338CA; font-size: 11px; font-weight: 700; border-radius: 6px;">IA</span>
                                    @elseif($email->template_key)
                                        <span style="padding: 4px 10px; background: #FEF3C7; color: #92400E; font-size: 11px; font-weight: 700; border-radius: 6px;">Modèle</span>
                                    @else
                                        <span style="padding: 4px 10px; background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 700; border-radius: 6px;">Manuel</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px;">
                                    @if($email->status === 'sent')
                                        <span style="padding: 4px 10px; background: #DCFCE7; color: #166534; font-size: 11px; font-weight: 700; border-radius: 6px;">Envoyé ✓</span>
                                    @else
                                        <span style="padding: 4px 10px; background: #FEE2E2; color: #991B1B; font-size: 11px; font-weight: 700; border-radius: 6px;">Échec</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 60px 20px; text-align: center; color: #94a3b8;">Aucun email envoyé pour le moment</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
