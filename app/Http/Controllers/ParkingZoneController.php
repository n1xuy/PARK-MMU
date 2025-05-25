<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ParkingZone;
use Illuminate\Http\Request;

class ParkingZoneController extends Controller
{
    public function getZoneStats(Request $request)
    {
    $request->validate(['zone_id' => 'required|exists:parking_zones,zone_number']);
    
    $zone = ParkingZone::where('zone_number', $request->zone_id)->first();
    
    $recentReports = $zone->reports()
        ->where('created_at', '>=', now()->subHours(3))
        ->get();

    $statusLabels = $recentReports->map(function ($report) {
        return match ($report->status) {
            1 => 'empty',
            2 => 'half_full',
            3 => 'full',
            default => 'unknown',
        };
    });

    $statusCounts = $statusLabels->countBy();

    $currentStatus = $statusCounts->sortDesc()->keys()->first() ?? 'empty';

    $lastReport = $zone->reports()->latest()->first();

    return response()->json([
        'zone_name' => $zone->name,
        'total_today' => $recentReports->count(),
        'total_empty' => $statusCounts['empty'] ?? 0,
        'total_half_full' => $statusCounts['half_full'] ?? 0,
        'total_full' => $statusCounts['full'] ?? 0,
        'last_report' => $lastReport,
        'current_status' => $currentStatus,
        'status_color' => $this->getStatusColor($currentStatus),
    ]);
}


    private function getStatusColor($status)
    {
        return match($status) {
            'full' => '#F44336', // Red
            'half_full' => '#FF9800', // Orange
            default => '#4CAF50' // Green
        };
    }

    public function show($zone_number) 
    {
        $zone = ParkingZone::where('zone_number', $zone_number)->first();
        
    if (!$zone) {
        abort(404); // Zone not found
    }

    return view('parkingdetail', [
        'currentZone' => $zone // Pass zone to view
    ]);
    }
}