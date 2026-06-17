<?php

namespace App\Filament\Pages;

use App\Models\Agency;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Parametres extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Paramètres';
    protected static ?string $title = 'Paramètres de l\'agence';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.parametres';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Agency::current()->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identité')->columns(2)->schema([
                    Forms\Components\TextInput::make('name')->label('Nom de l\'agence')->required(),
                    Forms\Components\TextInput::make('legal_name')->label('Raison sociale'),
                    Forms\Components\FileUpload::make('logo')->label('Logo')->image()->directory('agency'),
                    Forms\Components\TextInput::make('phone')->label('Téléphone')->tel(),
                    Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->tel(),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('address')->label('Adresse'),
                    Forms\Components\TextInput::make('city')->label('Ville'),
                    Forms\Components\TextInput::make('wilaya')->label('Wilaya'),
                    Forms\Components\TextInput::make('slogan')->label('Slogan')->placeholder('Votre route, notre passion')->columnSpanFull(),
                ]),

                Forms\Components\Section::make('Apparence du site')
                    ->description('Change ces 2 couleurs pour re-brander tout le site et l\'administration.')
                    ->columns(2)->schema([
                        Forms\Components\ColorPicker::make('color_primary')->label('Couleur primaire')->default('#006233'),
                        Forms\Components\ColorPicker::make('color_secondary')->label('Couleur secondaire (boutons)')->default('#D21034'),
                        Forms\Components\FileUpload::make('favicon')->label('Favicon')->image()->directory('agency')
                            ->helperText('Petite icône de l\'onglet du navigateur.'),
                        Forms\Components\FileUpload::make('og_image')->label('Image de partage (réseaux sociaux)')->image()->directory('agency')
                            ->helperText('Affichée quand on partage le lien sur Facebook/WhatsApp.'),
                    ]),

                Forms\Components\Section::make('Réseaux sociaux')->columns(3)->collapsed()->schema([
                    Forms\Components\TextInput::make('facebook')->label('Facebook')->url()->prefixIcon('heroicon-o-link'),
                    Forms\Components\TextInput::make('instagram')->label('Instagram')->url()->prefixIcon('heroicon-o-link'),
                    Forms\Components\TextInput::make('tiktok')->label('TikTok')->url()->prefixIcon('heroicon-o-link'),
                ]),

                Forms\Components\Section::make('Référencement (SEO)')
                    ->description('Comment le site apparaît sur Google et lors d\'un partage.')
                    ->columns(2)->collapsed()->schema([
                        Forms\Components\TextInput::make('meta_title')->label('Titre SEO')->columnSpanFull()
                            ->placeholder('Nom de l\'agence — Location de voitures')->maxLength(70),
                        Forms\Components\Textarea::make('meta_description')->label('Meta description')->columnSpanFull()
                            ->rows(2)->maxLength(180)->helperText('~160 caractères, le résumé affiché sur Google.'),
                        Forms\Components\TextInput::make('meta_keywords')->label('Mots-clés')->columnSpanFull()
                            ->placeholder('location voiture, alger, suv, berline'),
                    ]),

                Forms\Components\Section::make('Sections de la page d\'accueil')
                    ->description('Active/désactive et renomme les sections du site public.')
                    ->columns(2)->collapsed()->schema([
                        Forms\Components\Toggle::make('section_why')->label('Afficher « Pourquoi nous choisir »')->default(true),
                        Forms\Components\TextInput::make('why_title')->label('Titre')->placeholder('Pourquoi nous choisir'),
                        Forms\Components\Toggle::make('section_stats')->label('Afficher « Statistiques »')->default(true),
                        Forms\Components\TextInput::make('stats_title')->label('Titre')->placeholder('En chiffres'),
                        Forms\Components\Toggle::make('section_reviews')->label('Afficher « Avis clients »')->default(true),
                        Forms\Components\TextInput::make('reviews_title')->label('Titre')->placeholder('Avis clients'),
                        Forms\Components\Toggle::make('section_faq')->label('Afficher « FAQ »')->default(true),
                        Forms\Components\TextInput::make('faq_title')->label('Titre')->placeholder('Questions fréquentes'),
                        Forms\Components\Toggle::make('section_contact')->label('Afficher « Contact »')->default(true),
                        Forms\Components\TextInput::make('contact_title')->label('Titre')->placeholder('Nous contacter'),
                        Forms\Components\Toggle::make('section_vehicles')->label('Afficher les véhicules')->default(true)->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Paramètres métier')->columns(2)->schema([
                    Forms\Components\TextInput::make('currency')->label('Devise')->default('DZD')->maxLength(3),
                    Forms\Components\TextInput::make('vat_rate')->label('Taux de TVA (%)')->numeric(),
                    Forms\Components\TextInput::make('default_advance_percent')->label('Acompte par défaut (%)')->numeric(),
                    Forms\Components\TextInput::make('advance_expiry_hours')->label('Expiration acompte (heures)')->numeric(),
                    Forms\Components\Toggle::make('show_eur')->label('Afficher aussi les prix en euros (€)')
                        ->helperText('Pratique pour la diaspora. Ex : 9 000 DA / 50 €.')->live(),
                    Forms\Components\TextInput::make('eur_rate')->label('Taux : 1 € = ? DA')
                        ->numeric()->placeholder('180')
                        ->helperText('Ex : 180 si 1 € = 180 DA.')
                        ->visible(fn (Forms\Get $get) => $get('show_eur')),
                ]),

                Forms\Components\Section::make('Conditions de location')
                    ->description('Affichées sur le site et reprises dans le contrat PDF.')
                    ->schema([
                        Forms\Components\RichEditor::make('contract_terms')->label('Conditions (texte)')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'h2', 'h3', 'link', 'undo', 'redo'])
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('terms_pdf')->label('Conditions en PDF (optionnel)')
                            ->acceptedFileTypes(['application/pdf'])->directory('agency')
                            ->helperText('Le client pourra télécharger ce PDF.'),
                        Forms\Components\Toggle::make('require_terms')->label('Forcer l\'acceptation des conditions à la réservation')
                            ->helperText('Ajoute une case « J\'accepte les conditions » obligatoire.'),
                    ]),
                Forms\Components\Section::make('Paiement en ligne (PayPal)')
                    ->description('Permet au client de régler son acompte en ligne. PayPal ne gère pas le dinar : indiquez la devise et le taux de conversion.')
                    ->columns(2)->collapsed()->schema([
                        Forms\Components\Toggle::make('paypal_enabled')->label('Activer le paiement PayPal')->columnSpanFull(),
                        Forms\Components\Select::make('paypal_mode')->label('Mode')
                            ->options(['sandbox' => 'Test (sandbox)', 'live' => 'Production (réel)'])->default('sandbox'),
                        Forms\Components\Select::make('paypal_currency')->label('Devise PayPal')
                            ->options(['EUR' => 'EUR', 'USD' => 'USD', 'GBP' => 'GBP'])->default('EUR'),
                        Forms\Components\TextInput::make('paypal_rate')->label('Taux : 1 unité de devise = ? DA')
                            ->numeric()->helperText('Ex : 150 si 1 EUR = 150 DA. L\'acompte en DA sera converti automatiquement.'),
                        Forms\Components\TextInput::make('paypal_client_id')->label('Client ID')->password()->revealable(),
                        Forms\Components\TextInput::make('paypal_secret')->label('Secret')->password()->revealable()->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Modules')
                    ->description('Active les services proposés par l\'agence. Désactivés = location classique uniquement.')
                    ->columns(2)->schema([
                        Forms\Components\Toggle::make('delivery_enabled')->label('Livraison de véhicule'),
                        Forms\Components\Toggle::make('transfer_enabled')->label('Transfert / VTC'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        Agency::current()->update($this->form->getState());

        Notification::make()->title('Paramètres enregistrés')->success()->send();
    }
}
