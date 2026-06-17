<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\FaqItem;
use App\Models\Feature;
use App\Models\Stat;
use Illuminate\Database\Seeder;

/**
 * Contenu de démonstration du template (sections du site).
 * Sert de vitrine MedliCar et de point de départ pour une nouvelle agence.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['Meilleurs prix', 'Tarifs transparents, sans surprise', 'heroicon-o-banknotes'],
            ['Assistance 24/7', 'Une équipe joignable à tout moment', 'heroicon-o-clock'],
            ['Véhicules récents', 'Flotte entretenue et fiable', 'heroicon-o-sparkles'],
            ['Livraison rapide', 'Partout dans la wilaya', 'heroicon-o-truck'],
        ];
        foreach ($features as $i => [$title, $desc, $icon]) {
            Feature::firstOrCreate(['title' => $title], ['description' => $desc, 'icon' => $icon, 'sort_order' => $i]);
        }

        $stats = [
            ['500+', 'Clients satisfaits', '😀'],
            ['50', 'Véhicules', '🚗'],
            ['24/7', 'Support', '📞'],
            ['4.8', 'Note moyenne', '⭐'],
        ];
        foreach ($stats as $i => [$value, $label, $icon]) {
            Stat::firstOrCreate(['label' => $label], ['value' => $value, 'icon' => $icon, 'sort_order' => $i]);
        }

        $faq = [
            ['Quels documents pour louer ?', "Permis de conduire, pièce d'identité et une caution."],
            ['Puis-je payer en ligne ?', "Oui, l'acompte est payable par PayPal ; le reste à l'agence."],
            ['Livrez-vous le véhicule ?', 'Oui, livraison possible selon la zone de couverture.'],
        ];
        foreach ($faq as $i => [$q, $a]) {
            FaqItem::firstOrCreate(['question' => $q], ['answer' => $a, 'sort_order' => $i]);
        }

        Agency::current()->update([
            'slogan' => 'Votre route, notre passion',
            'meta_title' => 'MedliCar — Location de voitures en Algérie',
            'meta_description' => 'Louez une voiture facilement chez MedliCar : SUV, berlines, citadines. Réservation en ligne, prix en DA et €.',
            'meta_keywords' => 'location voiture, algérie, suv, berline, citadine',
            'color_primary' => '#006233',
            'color_secondary' => '#D21034',
        ]);
    }
}
