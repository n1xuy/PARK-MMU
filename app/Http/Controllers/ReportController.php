<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ParkingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{

    public function reportStatus(Request $request)
    {

        $validated = $request->validate([
        'zone_id' => 'required|exists:parking_zones,zone_number',
        'status' => 'required|integer|between:1,3'
        ]); 
        
        $zone = ParkingZone::where('zone_number', $validated['zone_id'])->first();

        $report = Report::create([
        'user_id' => auth()->id(),
        'parking_zone_id' => $zone->id, // Use database ID
        'status' => $validated['status'],
        'expires_at' => now()->addHour(3)
    ]);

    $zone = ParkingZone::find($report->parking_zone_id);
    $zone->update([
        'total_reports' => $zone->reports()->count(),
        'last_reported_at' => now(),
        'total_empty' => $zone->reports()->where('status',1)->count(),
        'total_half_full' => $zone->reports()->where('status',2)->count(),
        'total_full' => $zone->reports()->where('status',3)->count(),
    ]);

    return response()->json(['success' => true]);

    }
}