<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    /** Télécharge le contrat de location au format PDF. */
    public function download(Booking $booking)
    {
        return $this->pdf($booking)->download('contrat-' . $booking->reference . '.pdf');
    }

    /** Affiche le contrat dans le navigateur (aperçu). */
    public function stream(Booking $booking)
    {
        return $this->pdf($booking)->stream('contrat-' . $booking->reference . '.pdf');
    }

    private function pdf(Booking $booking)
    {
        $booking->loadMissing(['customer', 'vehicle.category']);

        return Pdf::loadView('pdf.contract', [
            'booking' => $booking,
            'agency' => Agency::current(),
        ])->setPaper('a4');
    }
}
