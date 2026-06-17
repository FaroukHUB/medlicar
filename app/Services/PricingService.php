<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\SeasonalRate;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Calcule le prix d'une location en appliquant :
 *  - les tarifs saisonniers (prix/jour spécifique sur une période),
 *  - les règles de prix (modificateurs % ou fixes selon durée/période).
 * Tout est piloté par les données (aucun prix en dur) → réutilisable par agence.
 */
class PricingService
{
    /**
     * @return array{days:int, base_price:float, season_surcharge:float, duration_discount:float, subtotal:float, rules:array}
     */
    public function quote(Vehicle $vehicle, Carbon $start, Carbon $end): array
    {
        $days = max(1, (int) ceil($start->copy()->startOfDay()->floatDiffInDays($end->copy()->startOfDay())) ?: 1);
        if ($days < 1) {
            $days = 1;
        }

        $daily = $vehicle->promoPrice(); // applique la promo éventuelle
        $plainBase = $days * $daily;

        // 1) Tarifs saisonniers : prix/jour spécifique jour par jour.
        $seasonals = SeasonalRate::where('is_active', true)
            ->where(function ($q) use ($vehicle) {
                $q->where('vehicle_id', $vehicle->id)->orWhere('category_id', $vehicle->category_id);
            })
            ->get();

        $base = 0.0;
        $period = CarbonPeriod::create($start->copy()->startOfDay(), $start->copy()->startOfDay()->addDays($days - 1));
        foreach ($period as $day) {
            $rate = $seasonals
                ->filter(fn ($r) => Carbon::parse($r->start_date)->lte($day) && Carbon::parse($r->end_date)->gte($day))
                ->sortByDesc(fn ($r) => $r->vehicle_id ? 1 : 0) // priorité au tarif véhicule sur celui de catégorie
                ->first();
            $base += $rate ? (float) $rate->price_per_day : $daily;
        }
        $seasonSurcharge = round($base - $plainBase, 2);

        // 2) Règles de prix (modificateurs).
        $rules = PricingRule::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('vehicle_id')->orWhere('vehicle_id', $vehicle->id))
            ->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $vehicle->category_id))
            ->orderByDesc('priority')
            ->get()
            ->filter(function ($rule) use ($days, $start, $end) {
                if ($rule->min_days && $days < $rule->min_days) {
                    return false;
                }
                if ($rule->max_days && $days > $rule->max_days) {
                    return false;
                }
                if ($rule->start_date && $rule->end_date) {
                    return $start->lte(Carbon::parse($rule->end_date)) && $end->gte(Carbon::parse($rule->start_date));
                }

                return true;
            });

        $subtotal = $base;
        $applied = [];
        foreach ($rules as $rule) {
            $delta = $rule->modifier_type === 'percentage'
                ? $subtotal * ((float) $rule->modifier_value) / 100
                : (float) $rule->modifier_value;
            $subtotal += $delta;
            $applied[] = ['name' => $rule->name, 'delta' => round($delta, 2)];
        }
        $subtotal = max(0, round($subtotal, 2));
        $durationDiscount = round($subtotal - $base, 2); // négatif = remise, positif = supplément

        return [
            'days' => $days,
            'base_price' => round($base, 2),
            'season_surcharge' => $seasonSurcharge,
            'duration_discount' => $durationDiscount,
            'subtotal' => $subtotal,
            'rules' => $applied,
        ];
    }
}
