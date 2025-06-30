<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZoneController extends Controller  
{
    public function show($zone)
    {
        if (!is_numeric($zone) || $zone < 1 || $zone > 15) {
            abort(404);
        }

        return view('home', [
            'zoneId' => $zone,
            'zoneName' => "PARKING ZONE " . $zone
        ]);
    }
} 