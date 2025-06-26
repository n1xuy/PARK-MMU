<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Report;
use App\Models\ParkingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ParkManageController;

class ParkingZoneController extends Controller
{
    const STATUS_EMPTY     = 1;
    const STATUS_HALF_FULL = 2;
    const STATUS_FULL      = 3;
    const STATUS_BLOCKED   = 4;

    const MIN_CONSENSUS_REPORTS = 3;
    const RECENT_REPORT_THRESHOLD_MINUTES = 30;
    const DNL_THRESHOLD_MINUTES = 30;

    //set coordinates for each parking zone
    private function getZoneCoordinates()
    {
        return [
            1 => ['lat' => 2.92824, 'lng' => 101.64086, 'name' => 'FCI Parking'],
            2 => ['lat' => 2.92951, 'lng' => 101.64075, 'name' => 'FOM Public Parking'],
            3 => ['lat' => 2.93038, 'lng' => 101.64132, 'name' => 'FOM Parking (Staff)'],
            4 => ['lat' => 2.93016, 'lng' => 101.64275, 'name' => 'Visitor Parking'],
            5 => ['lat' => 2.92776, 'lng' => 101.64315, 'name' => 'StarBee Parking'],
            6 => ['lat' => 2.92797, 'lng' => 101.64350, 'name' => 'MMU Stadium Parking'],
            7 => ['lat' => 2.92689, 'lng' => 101.64457, 'name' => 'MMU Stadium Parking 2'],
            8 => ['lat' => 2.92440, 'lng' => 101.64567, 'name' => 'HB1 Parking'],
            9 => ['lat' => 2.92554, 'lng' => 101.64111, 'name' => 'FOE Parking'],
            10 => ['lat' => 2.92742, 'lng' => 101.64112, 'name' => 'Library Parking'],
            11 => ['lat' => 2.92758, 'lng' => 101.64005, 'name' => 'FMD Parking'],
            12 => ['lat' => 2.9274014, 'lng' => 101.6426726, 'name' => 'FCM Parking'],
            13 => ['lat' => 2.925160, 'lng' => 101.6458500, 'name' => 'HB1 A Parking'],
            14 => ['lat' => 2.92475, 'lng' => 101.6431829, 'name' => 'Surau Parking'],
            15 => ['lat' => 2.92595, 'lng' => 101.6437733, 'name' => 'HB4 C Parking'],
            16 => ['lat' => 2.92611, 'lng' => 101.64039, 'name' => 'Field Parking'],
            17 => ['lat' => 2.92900, 'lng' => 101.6428055, 'name' => 'Security Parking'],
            18 => ['lat' => 2.92551, 'lng' => 101.64541, 'name' => 'Canteen Parking'],
            19 => ['lat' => 2.92842, 'lng' => 101.63918, 'name' => 'MSC Parking (Staff)'],
            20 => ['lat' => 2.9251259, 'lng' => 101.642052, 'name' => 'STAD Parking (Staff)'],
            21 => ['lat' => 2.9265186, 'lng' => 101.6430174, 'name' => 'FCM Parking (Staff)'],
            22 => ['lat' => 2.9264535, 'lng' => 101.6409169, 'name' => 'FOE Parking (Staff)'],
            23 => ['lat' => 2.92770, 'lng' => 101.63925, 'name' => 'FMD Parking (Staff)'],
        ];
    }

    public function show($zoneNumber)
    {
        $zone = ParkingZone::where('zone_number', $zoneNumber)->firstOrFail();

        // Enhanced blocking check
        if ($zone->isBlocked()) {
            $reason = $zone->block_reason ?: 'This parking zone is currently blocked.';
            $blockDate = $zone->block_date ? \Carbon\Carbon::parse($zone->block_date)->format('M d, Y') : '';
            $blockStart = $zone->block_start_time ? \Carbon\Carbon::parse($zone->block_start_time)->format('h:i A') : '';
            $blockEnd = $zone->block_end_time ? \Carbon\Carbon::parse($zone->block_end_time)->format('h:i A') : '';
            
            $message = "Zone P{$zoneNumber} is currently blocked. Reason: {$reason}";
            if ($blockDate && $blockStart && $blockEnd) {
                $message .= " (Date: {$blockDate}, Time: {$blockStart} - {$blockEnd})";
            }
            
            return redirect()->route('home')->with('error', $message);
        }

        // Get today's reports
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        // Calculate totals from actual reports
        $totals = [
            'empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
            'total' => $reportsToday->count()
        ];

        // Get last report
        $lastReport = $reportsToday->sortByDesc('created_at')->first();

        // ADD THIS: Get zone coordinates
        $coordinates = $this->getZoneCoordinates();
        $zoneCoords = $coordinates[$zoneNumber] ?? null;

        return view('parkingdetail', [
            'zone' => $zone,
            'zoneNumber' => $zoneNumber,
            'zoneName' => $zoneCoords ? $zoneCoords['name'] : "Parking Zone {$zoneNumber}",
            'currentStatus' => $zone->status,
            'status_color' => $zone->status_color,
            'totals' => $totals,
            'lastReport' => $lastReport,
            'coordinates' => $zoneCoords 
        ]);
    }

    public function getZoneStats(Request $request)
    {
        $request->validate([ 
            'zone_id' => 'required|exists:parking_zones,zone_number',
        ]);

        $zone = ParkingZone::where('zone_number', $request->zone_id)->firstOrFail();

        // Get today's reports
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        $lastReport = $reportsToday->sortByDesc('created_at')->first();

        $status = $zone->status_override 
        ? $zone->status 
        : $this->calculateReliableStatus($zone);

        if ($zone->zone_type === 'staff') {
            $statusLabel = 'staff';
        } else {
            $statusLabel = $this->getStatusLabel($status);
        }

         // ADD THIS: Get zone coordinates
        $coordinates = $this->getZoneCoordinates();
        $zoneCoords = $coordinates[$zone->zone_number] ?? null;

        return response()->json([
            'zone_name' => "{$zone->name}",
            'zone_type' => $zone->zone_type,
            'status' => $zone->status,
            'status_color' => $zone->status_color,
            'total_today' => $reportsToday->count(),
            'total_empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'total_half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'total_full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
            'last_report' => $lastReport ? [
                'time' => $lastReport->created_at->format('h:i A'),
                'date' => $lastReport->created_at->format('M d, Y') 
            ] : null,
            'coordinates' => $zoneCoords // ADD THIS LINE
        ]);
    }

    // FIXED: Add coordinates to the response and fix logic bugs
    public function getAllZoneStatuses()
    {
        app(\App\Http\Controllers\ParkManageController::class)->autoUnblockExpiredZones();

        $zones = ParkingZone::all();
        $coordinates = $this->getZoneCoordinates();
        $result = [];
        
        foreach ($zones as $zone) {
            $zoneCoords = $coordinates[$zone->zone_number] ?? null;
        
            if($zoneCoords){
                $isBlocked = $zone->isBlocked();
                $futureBlock = $zone->nextFutureBlock();
                
                // FIXED: Proper status calculation
                if ($isBlocked) {
                    $status = self::STATUS_BLOCKED;
                    $statusLabel = 'blocked';
                } else {
                    // FIXED: Calculate proper status for non-blocked zones
                    $status = $this->calculateReliableStatus($zone);
                    if ($zone->zone_type === 'staff') {
                        $statusLabel = 'staff';
                    } else {
                        $statusLabel = $this->getStatusLabel($status);
                    }
                }

                $reportsToday = Report::where('parking_zone_id', $zone->id)
                    ->whereDate('created_at', today())
                    ->get();
                
                    $lastReport = $reportsToday->sortByDesc('updated_at')->first();
                    $diff = $lastReport ? $lastReport->updated_at->diffInMinutes(now()) : null;
                    Log::info(
                        'Zone '.$zone->zone_number.
                        ' | Last report: '.($lastReport?->updated_at).
                        ' | Now: '.now().
                        ' | Diff: '.($diff !== null ? $diff : 'N/A')
                    );

                    if (
                        $lastReport &&
                        in_array($statusLabel, ['empty', 'half_full', 'full']) &&
                        $diff > self::DNL_THRESHOLD_MINUTES
                    ) {
                        $statusLabel = 'dnl_' . $statusLabel;
                    }

                $zoneArr = [
                    'zone_number' => $zone->zone_number,
                    'zone_id' => $zone->id,
                    'zone_name' => $zoneCoords['name'], // FIXED: Removed duplicate
                    'zone_type' => $zone->zone_type,
                    'current_status' => $statusLabel,
                    'status_color' => $this->getStatusColor($statusLabel),
                    'is_blocked' => $isBlocked,
                    'block_reason' => $isBlocked ? $zone->block_reason : null,
                    'block_date' => $isBlocked ? $zone->block_date : null,
                    'block_start_time' => $isBlocked ? $zone->block_start_time : null,
                    'block_end_time' => $isBlocked ? $zone->block_end_time : null,
                    'latitude' => $zoneCoords ? $zoneCoords['lat'] : null,
                    'longitude' => $zoneCoords ? $zoneCoords['lng'] : null,
                ];
                
                if ($futureBlock) {
                    $zoneArr['future_block'] = [
                        'date' => $futureBlock->date,
                        'start_time' => $futureBlock->start_time,
                        'end_time' => $futureBlock->end_time,
                        'reason' => $futureBlock->reason,
                        'schedule_type' => $futureBlock->schedule_type,
                        'is_recurring' => $futureBlock->is_recurring,
                        'weekly_days' => $futureBlock->is_recurring ? json_decode($futureBlock->weekly_days, true) : null,
                        'recurring_end_date' => $futureBlock->recurring_end_date,
                    ];
                }
                $result[] = $zoneArr;
            }
        }

        return response()->json($result);
    }

    // FIXED: Proper status calculation for map zones
    public function getZonesForMap()
    {
        app(\App\Http\Controllers\ParkManageController::class)->autoUnblockExpiredZones();

        $zones = ParkingZone::all();
        $coordinates = $this->getZoneCoordinates();
        $result = [];
        
        foreach ($zones as $zone) {
            $zoneCoords = $coordinates[$zone->zone_number] ?? null;
            
            if ($zoneCoords) { // Only include zones with coordinates
                $isBlocked = $zone->isBlocked();
                $futureBlock = $zone->nextFutureBlock();
                
                // FIXED: Proper status calculation
                if ($isBlocked) {
                    $status = self::STATUS_BLOCKED;
                    $statusLabel = 'blocked';
                } else {
                    $status = $zone->status_override 
                        ? $zone->status 
                        : $this->calculateReliableStatus($zone);
                    
                    if ($zone->zone_type === 'staff') {
                        $statusLabel = 'staff';
                    } else {
                        $statusLabel = $this->getStatusLabel($status);
                    }
                }

                $reportsToday = Report::where('parking_zone_id', $zone->id)
                ->whereDate('created_at', today())
                ->get();

                $lastReport = $reportsToday->sortByDesc('updated_at')->first();
                $diff = $lastReport ? $lastReport->updated_at->diffInMinutes(now()) : null;
                Log::info(
                    'Zone '.$zone->zone_number.
                    ' | Last report: '.($lastReport?->updated_at).
                    ' | Now: '.now().
                    ' | Diff: '.($diff !== null ? $diff : 'N/A')
                );

                if (
                    $lastReport &&
                    in_array($statusLabel, ['empty', 'half_full', 'full']) &&
                    $diff > self::DNL_THRESHOLD_MINUTES
                ) {
                    $statusLabel = 'dnl_' . $statusLabel;
                }
                
                $zoneArr = [
                    'zone_number' => $zone->zone_number,
                    'zone_id' => $zone->id,
                    'zone_name' => $zoneCoords['name'],
                    'zone_type' => $zone->zone_type,
                    'latitude' => $zoneCoords['lat'],
                    'longitude' => $zoneCoords['lng'],
                    'current_status' => $statusLabel,
                    'status_color' => $this->getStatusColor($statusLabel),
                    'is_blocked' => $isBlocked,
                    'block_reason' => $isBlocked ? $zone->block_reason : null,
                    'block_date' => $isBlocked ? $zone->block_date : null,
                    'block_start_time' => $isBlocked ? $zone->block_start_time : null,
                    'block_end_time' => $isBlocked ? $zone->block_end_time : null,
                    'status_override' => $zone->status_override
                ];
                
                if ($futureBlock) {
                    $zoneArr['future_block'] = [
                        'date' => $futureBlock->date,
                        'start_time' => $futureBlock->start_time,
                        'end_time' => $futureBlock->end_time,
                        'reason' => $futureBlock->reason,
                        'schedule_type' => $futureBlock->schedule_type,
                        'is_recurring' => $futureBlock->is_recurring,
                        'weekly_days' => $futureBlock->weekly_days,
                        'recurring_end_date' => $futureBlock->recurring_end_date,
                    ];
                }
                $result[] = $zoneArr;
            }
        }

        return response()->json($result);
    }

    public function calculateReliableStatus(ParkingZone $zone)
    {
        if ($zone->status_override) {
            return $zone->status;
        }

        // FIXED: Don't return blocked status here - let the calling method handle it
        // This method should only calculate status based on reports

        $reports = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        return $this->getConsensusStatus($reports)
            ?? $this->getRecentReportStatus($reports)
            ?? $this->getFirstReportStatus($reports)
            ?? self::STATUS_EMPTY;
    }

    public function getConsensusStatus($reports)
    {
        $reports = collect($reports);
        if ($reports->count() < self::MIN_CONSENSUS_REPORTS) {
            return null;
        }

        $counts = $reports->countBy('status')->sortDesc();

        return $counts->first() >= self::MIN_CONSENSUS_REPORTS
            ? $counts->keys()->first()
            : null;
    }

    public function getRecentReportStatus($reports)
    {
        $reports = collect($reports);
        $threshold = Carbon::now()->subMinutes(self::RECENT_REPORT_THRESHOLD_MINUTES);
    
        $recentReport = $reports->filter(function ($report) use ($threshold) {
            return $report->created_at >= $threshold;
        })->sortByDesc('created_at')->first();

        return $recentReport?->status;
    }

    public function getFirstReportStatus($reports)
    {
        $reports = collect($reports);
        $firstReport = $reports->sortBy('created_at')->first();
        return $firstReport?->status;
    }

    public function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_EMPTY:
                return 'empty';
            case self::STATUS_HALF_FULL:
                return 'half_full';
            case self::STATUS_FULL:
                return 'full';
            case self::STATUS_BLOCKED:
                return 'blocked';
            default:
                return 'empty'; // FIXED: Return 'empty' instead of empty string for consistency
        }
    }

    public function getStatusColor($statusLabel)
    {
        switch ($statusLabel) {
            case 'empty':
                return '#28a745'; // Green
            case 'half_full':
                return '#ffc107'; // Yellow
            case 'full':
                return '#dc3545'; // Red
            case 'blocked':
                return '#6c757d'; // Gray
            case 'staff':
                return '#2196F3'; // Blue
            default:
                return '#28a745'; // Default green for available zones
        }
    }

    private function getUserReport(ParkingZone $zone)
    {
        return Report::where('user_id', auth()->id())
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->first();
    }
}