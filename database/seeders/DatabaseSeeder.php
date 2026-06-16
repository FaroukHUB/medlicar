<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // L'agence unique
        Agency::firstOrCreate(['id' => 1], [
            'name' => 'Medlicar',
            'currency' => 'DZD',
            'timezone' => 'Africa/Algiers',
            'default_advance_percent' => 30,
            'advance_expiry_hours' => 48,
            'delivery_enabled' => false,
            'transfer_enabled' => false,
        ]);

        // Compte propriétaire (accès back-office)
        User::firstOrCreate(['email' => 'owner@medlicar.test'], [
            'name' => 'Propriétaire',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // Catégories de base
        foreach (['Économique', 'Berline', 'SUV', 'Luxe', 'Utilitaire'] as $i => $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'sort_order' => $i,
            ]);
        }

        // Quelques marques
        foreach (['Volkswagen', 'Renault', 'Peugeot', 'Hyundai', 'Toyota', 'Dacia'] as $i => $name) {
            Brand::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'sort_order' => $i,
            ]);
        }

        // Extras facturables courants
        $options = [
            ['GPS', 'per_day', 300],
            ['Siège bébé', 'per_booking', 1000],
            ['Conducteur additionnel', 'per_booking', 2000],
            ['Assurance tous risques', 'per_day', 1500],
        ];
        foreach ($options as $i => [$name, $type, $price]) {
            Option::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'price_type' => $type,
                'price' => $price,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
