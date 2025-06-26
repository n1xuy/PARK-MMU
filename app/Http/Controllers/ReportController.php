<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ParkingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Status constants
    const STATUS_EMPTY = 1;
    const STATUS_HALF_FULL = 2;
    const STATUS_FULL = 3;

    const MIN_CONSENSUS_REPORTS = 3;
    const RECENT_REPORT_THRESHOLD_MINUTES = 30;
    const DNL_THRESHOLD_MINUTES = 30;

    /**
     * Submit a new or update an existing report
     */
    public function submitReport(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:parking_zones,zone_number',
            'status' => 'required|integer|in:1,2,3'
        ]);

        $zone = ParkingZone::where('zone_number', $validated['zone_id'])->firstOrFail();

        // Check existing report
        $existingReport = Report::where('user_id', Auth::id())
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a report for this zone today.'
            ], 400);
        }

        // Create report
        $report = Report::create([
            'user_id' => Auth::id(),
            'parking_zone_id' => $zone->id,
            'status' => $validated['status'],
            'expires_at' => now()->addMinutes(30),
        ]);

        // Update parking zone status and refresh counts
        $this->updateParkingZoneStatus($zone);
        $zone->refresh();

        // Get updated totals from today's reports
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        $totals = [
            'total' => $reportsToday->count(),
            'empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully',
            'status' => $zone->status,
            'status_color' => $zone->status_color,
            'totals' => $totals,
            'last_report' => [
                'time' => $report->created_at->format('h:i A'),
                'date' => $report->created_at->format('M d, Y')
            ]
        ]);
    }

    public function updateReport(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:parking_zones,zone_number',
            'status' => 'required|integer|in:1,2,3'
        ]);

        $zone = ParkingZone::where('zone_number', $validated['zone_id'])->firstOrFail();

        // Find existing report
        $existingReport = Report::where('user_id', Auth::id())
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->first();

        if (!$existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'No existing report found to update.'
            ], 404);
        }

        // Update the existing report
        $existingReport->update([
            'status' => $validated['status'],
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ]);

        // Update parking zone status and refresh counts
        $this->updateParkingZoneStatus($zone);
        $zone->refresh();

        // Get updated totals from today's reports
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        $totals = [
            'total' => $reportsToday->count(),
            'empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Report updated successfully',
            'status' => $zone->status,
            'status_color' => $zone->status_color,
            'totals' => $totals,
            'last_report' => [
                'time' => $existingReport->updated_at->format('h:i A'),
                'date' => $existingReport->updated_at->format('M d, Y')
            ]
        ]);
    }

    protected function updateParkingZoneStatus(ParkingZone $zone)
    {
        // Get today's reports
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        // Calculate reliable status
        $reliableStatus = $this->calculateReliableStatus($zone->id);
        $statusLabel = $this->getStatusLabel($reliableStatus);

        // Update zone with new status and counts
        $zone->update([
            'status' => $statusLabel,
            'last_reported_at' => now(),
            'total_reports' => $reportsToday->count(),
            'total_empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'total_half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'total_full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
        ]);
    }

    protected function getStatusLabel($status)
    {
        return match ($status) {
            self::STATUS_FULL => 'full',
            self::STATUS_HALF_FULL => 'half_full',
            default => 'empty',
        };
    }

    public function checkUserReport($zoneNumber)
    {
        if (!Auth::check()) {
            return response()->json(['hasReport' => false]);
        }

        $zone = ParkingZone::where('zone_number', $zoneNumber)->firstOrFail();

        $userReport = Report::where('user_id', Auth::id())
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->first();

        if ($userReport) {
            return response()->json([
                'hasReport' => true,
                'status' => $userReport->status
            ]);
        }
        
        return response()->json(['hasReport' => false]);
    }

    /**
     * Delete the current user's report for a specific zone
     */
    public function deleteReport($zoneNumber)
    {
        $zone = ParkingZone::where('zone_number', $zoneNumber)->firstOrFail();

        $report = Report::where('user_id', Auth::id())
            ->where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->first();

        if (!$report) {
            return response()->json(['message' => 'No report found to delete'], 404);
        }

        // Delete the report
        $report->delete();
        
        // Update parking zone status and counts
        $this->updateParkingZoneStatus($zone);
        $zone->refresh();

        // Get updated totals
        $reportsToday = Report::where('parking_zone_id', $zone->id)
            ->whereDate('created_at', today())
            ->get();

        $totals = [
            'total' => $reportsToday->count(),
            'empty' => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
            'half_full' => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
            'full' => $reportsToday->where('status', self::STATUS_FULL)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Report deleted successfully',
            'status' => $zone->status,
            'status_color' => $zone->status_color,
            'totals' => $totals
        ]);
    }

    /**
     * Algorithm to determine the most reliable parking status
     */
    protected function calculateReliableStatus($zoneId)
    {
        $consensusStatus = $this->getConsensusStatus($zoneId);
        if ($consensusStatus !== null) {
            return $consensusStatus;
        }

        $recentReportStatus = $this->getRecentReportStatus($zoneId);
        if ($recentReportStatus !== null) {
            return $recentReportStatus;
        }

        $firstReportStatus = $this->getFirstReportStatus($zoneId);
        if ($firstReportStatus !== null) {
            return $firstReportStatus;
        }

        return self::STATUS_EMPTY; // Default status
    }

    /**
     * Algorithm 1: Get status if we have consensus from 3+ reports
     */
    protected function getConsensusStatus($zoneId)
    {
        $reportsToday = Report::where('parking_zone_id', $zoneId)
            ->whereDate('created_at', today())
            ->get();

        if ($reportsToday->count() >= self::MIN_CONSENSUS_REPORTS) {
            $statusCounts = [
                self::STATUS_EMPTY => $reportsToday->where('status', self::STATUS_EMPTY)->count(),
                self::STATUS_HALF_FULL => $reportsToday->where('status', self::STATUS_HALF_FULL)->count(),
                self::STATUS_FULL => $reportsToday->where('status', self::STATUS_FULL)->count(),
            ];

            arsort($statusCounts);
            $topStatus = key($statusCounts);

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
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'asc')
            ->first();

        return $firstReport?->status;
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

        return $recentReport?->status;
    }
}