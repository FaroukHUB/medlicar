<?php

namespace App\Http\Middleware;

use App\Models\VisitEvent;
use Closure;
use Illuminate\Http\Request;

class TrackVisit
{
    /** Enregistre une visite des pages publiques (hors admin, ajax, assets). */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $path = $request->path();
            $skip = $request->method() !== 'GET'
                || $request->ajax()
                || $request->is('app', 'app/*', 'livewire/*', 'sitemap.xml', 'storage/*')
                || str_contains($path, '.');

            if (! $skip && $response->getStatusCode() === 200) {
                VisitEvent::create(['type' => 'visit', 'label' => '/' . ltrim($path, '/')]);
            }
        } catch (\Throwable $e) {
            // tracking ne doit jamais casser la page
        }

        return $response;
    }
}
