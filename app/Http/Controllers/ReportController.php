<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ParkingZone;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Constants for status values
    const STATUS_EMPTY = 1;
    const STATUS_HALF_FULL = 2;
    const STATUS_FULL = 3;
    
    // Reliability thresholds
    const MIN_CONSENSUS_REPORTS = 3;
    const RECENT_REPORT_THRESHOLD_MINUTES = 30;

    public function reportStatus(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|integer|exists:parking_zones,id',
            'status' => 'required|integer|in:1,2,3',
        ]);

        $user = Auth::user();
        $zoneId = $request->zone_id;
        $status = $request->status;

        // Check if user already reported today
        $existingReport = Report::where('user_id', $user->id)
            ->where('parking_zone_id', $zoneId)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($existingReport) {
            $existingReport->status = $status;
            $existingReport->save();
        } else {
            Report::create([
                'user_id' => $user->id,
                'parking_zone_id' => $zoneId,
                'status' => $status,
            ]);
        }

        $this->updateParkingZoneStats($zoneId);

        return response()->json(['message' => 'Report submitted successfully']);
    }

    public function deleteReport($zoneNumber)
    {
        $user = Auth::user();
        $zone = ParkingZone::where('zone_number', $zoneNumber)->firstOrFail();

        $report = Report::where('user_id', $user->id)
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($report) {
            $report->delete();
            $this->updateParkingZoneStats($zone->id);
            return redirect()->back()->with('success', 'Your report has been deleted.');
        }

        return redirect()->back()->with('error', 'No report found to delete.');
    }

    public function zoneStats(Request $request)
    {
        $request->validate(['parking_zone_id' => 'required|exists:parking_zones,zone_number']);
        
        $zone = ParkingZone::where('zone_number', $request->parking_zone_id)->firstOrFail();

        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $totalEmpty = $reportsToday->where('status', self::STATUS_EMPTY)->count();
        $totalHalfFull = $reportsToday->where('status', self::STATUS_HALF_FULL)->count();
        $totalFull = $reportsToday->where('status', self::STATUS_FULL)->count();

        $lastReport = Report::where('parking_zone_id', $zone->id)
            ->latest('created_at')
            ->first();

        // Calculate reliable status based on algorithms
        $reliableStatus = $this->calculateReliableStatus($zone->id);
        
        return response()->json([
            'total_today' => $reportsToday->count(),
            'total_empty' => $totalEmpty,
            'total_half_full' => $totalHalfFull,
            'total_full' => $totalFull,
            'reliable_status' => $reliableStatus,
            'last_report' => $lastReport ? [
                'created_at' => $lastReport->created_at
            ] : null,
        ]);
    }

    protected function updateParkingZoneStats($zoneId)
    {
        $zone = ParkingZone::find($zoneId);

        if (!$zone) return;

        $reportsToday = Report::where('parking_zone_id', $zoneId)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $zone->total_reports = $reportsToday->count();
        $zone->total_empty = $reportsToday->where('status', self::STATUS_EMPTY)->count();
        $zone->total_half_full = $reportsToday->where('status', self::STATUS_HALF_FULL)->count();
        $zone->total_full = $reportsToday->where('status', self::STATUS_FULL)->count();
        $zone->last_reported_at = $reportsToday->max('created_at');
        
        // Update reliable status
        $zone->reliable_status = $this->calculateReliableStatus($zoneId);
        $zone->reliable_status_updated_at = now();

        $zone->save();
    }

    public function show($zoneNumber)
    {
        $zone = ParkingZone::where('zone_number', $zoneNumber)->firstOrFail();
        return view('parkingdetail', ['zone' => $zone]);
    }

    /**
     * Calculate the reliable status based on multiple algorithms
     */
    protected function calculateReliableStatus($zoneId)
    {
        $consensusStatus = $this->getConsensusStatus($zoneId);
        if ($consensusStatus !== null) {
            return $consensusStatus;
        }

        $firstReportStatus = $this->getFirstReportStatus($zoneId);
        if ($firstReportStatus !== null) {
            return $firstReportStatus;
        }

        $recentReportStatus = $this->getRecentReportStatus($zoneId);
        if ($recentReportStatus !== null) {
            return $recentReportStatus;
        }

        return null; // No reliable status could be determined
    }

    /**
     * Algorithm 1: Get status if we have consensus from 3+ reports
     */
    protected function getConsensusStatus($zoneId)
    {
        $reportsToday = Report::where('parking_zone_id', $zoneId)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        if ($reportsToday->count() >= self::MIN_CONSENSUS_REPORTS) {
            $statusCounts = [
                self::STATUS_EMPTY => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
                self::STATUS_HALF_FULL => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
                self::STATUS_FULL => $reportsToday->where('status', self::STATUS_FULL)->count(),
            ];

            arsort($statusCounts);
            $topStatus = key($statusCounts);
            
            // Only return if the top status has at least MIN_CONSENSUS_REPORTS agreeing
            if ($statusCounts[$topStatus] >= self::MIN_CONSENSUS_REPORTS) {
                return $topStatus;
            }
        }

        return null;
    }

    /**
     * Algorithm 2: Get the first report of the day
     */
    protected function getFirstReportStatus($zoneId)
    {
        $firstReport = Report::where('parking_zone_id', $zoneId)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'asc')
            ->first();

        return $firstReport ? $firstReport->status : null;
    }

    /**
     * Algorithm 3: Get status from a report within the last 30 minutes
     */
    protected function getRecentReportStatus($zoneId)
    {
        $threshold = Carbon::now()->subMinutes(self::RECENT_REPORT_THRESHOLD_MINUTES);
        
        $recentReport = Report::where('parking_zone_id', $zoneId)
            ->where('created_at', '>=', $threshold)
            ->latest('created_at')
            ->first();

        return $recentReport ? $recentReport->status : null;
    }
}