<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Models\Report;
use App\Models\SystemLog;
use App\Models\ParkManage;
use App\Models\ParkingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Attribute\Cache;

class ParkManageController extends Controller
{
    const STATUS_EMPTY     = 1;
    const STATUS_HALF_FULL = 2;
    const STATUS_FULL      = 3;
    const STATUS_BLOCKED   = 4;
    
    public function index()
    {
        $zones = ParkingZone::orderBy('zone_number')->paginate(10);
        return view('parkmanagement', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:parking_zones,id',
            'reason' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'schedule_type' => 'required|in:single,weekly',
            'weekly_days' => 'array|nullable',
            'weekly_days.*' => 'integer|between:1,7',
            'recurring_end_date' => 'nullable|date|after_or_equal:date',
        ]);

        $zone = ParkingZone::findOrFail($validated['zone_id']);

        // Check for overlapping blocks
        $overlapping = $this->checkForOverlappingBlocks($zone, $validated);
        
        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'This zone is already blocked during the specified time.',
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            if ($validated['schedule_type'] === 'single') {
                $this->createSingleBlock($zone, $validated);
            } else {
                $this->createRecurringBlock($zone, $validated);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Zone blocked successfully.',
                'zone' => [
                    'id' => $zone->id,
                    'zone_id' => $zone->zone_id,
                    'number' => $zone->zone_number,
                    'status' => 'blocked',
                    'reason' => $validated['reason']
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create block: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function checkForOverlappingBlocks($zone, $validated)
    {
        $blockDate = Carbon::parse($validated['date']);
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];

        // Check single blocks
        $overlapping = ParkManage::where('zone_id', $zone->id)
            ->where('is_cancelled', false)
            ->where('is_recurring', false)
            ->where('date', $blockDate->format('Y-m-d'))
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    // New block starts during existing block
                    $q->where('start_time', '<=', $startTime)
                    ->where('end_time', '>', $startTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New block ends during existing block
                    $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>=', $endTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New block completely contains existing block
                    $q->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
                });
            })
            ->exists();

        if (!$overlapping && $validated['schedule_type'] === 'weekly') {
            // Check recurring blocks
            $dayOfWeek = $blockDate->dayOfWeek === 0 ? 7 : $blockDate->dayOfWeek;
            
            $overlapping = ParkManage::where('zone_id', $zone->id)
                ->where('is_cancelled', false)
                ->where('is_recurring', true)
                ->where('recurring_start_date', '<=', $blockDate->format('Y-m-d'))
                ->where(function($q) use ($blockDate) {
                    $q->whereNull('recurring_end_date')
                    ->orWhere('recurring_end_date', '>=', $blockDate->format('Y-m-d'));
                })
                ->whereJsonContains('weekly_days', $dayOfWeek)
                ->where(function($query) use ($startTime, $endTime) {
                    $query->where(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                    });
                })
                ->exists();
        }
        return $overlapping;
    }

    private function createSingleBlock($zone, $validated)
    {
        $blockDate = Carbon::parse($validated['date']);
        $blockStartDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $blockEndDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        $now = Carbon::now();
        $today = Carbon::today();

        // Create single block record
        ParkManage::create([
            'zone_id' => $zone->id,
            'reason' => $validated['reason'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'schedule_type' => 'single',
            'is_recurring' => false,
        ]);

        $shouldBlockNow = false;

        if ($blockDate->equalTo($today)) {
            // Block is scheduled for today - check if we're within the time range
            if ($now->between($blockStartDateTime, $blockEndDateTime)) {
                $shouldBlockNow = true;
            }
        } elseif ($blockDate->lessThan($today)) {
            // Block date is in the past but within time range today (edge case)
            $todayBlockStart = Carbon::parse($today->format('Y-m-d') . ' ' . $validated['start_time']);
            $todayBlockEnd = Carbon::parse($today->format('Y-m-d') . ' ' . $validated['end_time']);
            
            if ($now->between($todayBlockStart, $todayBlockEnd)) {
                $shouldBlockNow = true;
            }
        }
        if ($shouldBlockNow) {
        // Block the zone immediately since we're within the scheduled time
        $zone->update([
            'is_blocked' => true,
            'current_status' => self::STATUS_BLOCKED,
            'status' => 'blocked', 
            'block_reason' => $validated['reason'],
            'block_date' => $validated['date'],
            'block_start_time' => $validated['start_time'],
            'block_end_time' => $validated['end_time'],
            'block_expires_at' => $blockEndDateTime,
            'status_override' => false,
        ]);
        $this->createReport($zone);
        $this->logBlockAction($zone, $validated['reason'], 'Block (Active)');
        }else {
        // This is a future block - don't block the zone yet, just schedule it
        // The zone will be blocked when the scheduled time arrives (via processRecurringBlocks or a scheduler)
        
        // Don't update the zone's blocking status, just log the scheduling
        $this->logBlockAction($zone, $validated['reason'], 'Block (Scheduled)');
        }
    }

    private function createRecurringBlock($zone, $validated)
    {
        // Create recurring block record
        $recurringBlock = ParkManage::create([
            'zone_id' => $zone->id,
            'reason' => $validated['reason'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'schedule_type' => 'weekly',
            'weekly_days' => $validated['weekly_days'],
            'recurring_start_date' => $validated['date'],
            'recurring_end_date' => $validated['recurring_end_date'],
            'is_recurring' => true,
        ]);

        // Check if today matches any of the recurring days and block immediately if so
        $today = Carbon::today();
        $now = Carbon::now();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;

        $blockStartDateTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $blockEndDateTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        
         if (in_array($todayDayOfWeek, $validated['weekly_days']) && 
            $today->format('Y-m-d') >= $validated['date'] &&
            $now->format('H:i') >= $validated['start_time'] && 
            $now->format('H:i') <= $validated['end_time']) {
            
            $zone->update([
                'is_blocked' => true,
                'current_status' => self::STATUS_BLOCKED,
                'status' => 'blocked', 
                'block_reason' => $validated['reason'],
                'block_date' => $validated['date'],
                'block_start_time' => $validated['start_time'],
                'block_end_time' => $validated['end_time'], 
                'block_expires_at' => $blockEndDateTime,
                'status_override' => false, // FIX: Clear override when blocking
            ]);

            $this->createReport($zone);
        }

        $this->logBlockAction($zone, $validated['reason'], 'Recurring Block');
    }

    private function logBlockAction($zone, $reason, $action)
    {
        $admin = Auth::guard('admin')->user();
        
        SystemLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'description' => "Admin '{$admin->username}' created {$action} for zone P{$zone->zone_number}: {$reason}",
            'action' => $action,
            'model' => 'ParkingZone',
        ]);
    }

    private function createReport($zone)
    {
        $adminId = Auth::guard('admin')->id();
        
        Report::create([
            'user_id' => $adminId ?: 1,
            'parking_zone_id' => $zone->id,
            'status' => self::STATUS_BLOCKED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function unblock($zoneId)
    {   
        try {
            DB::beginTransaction();

            $zone = ParkingZone::findOrFail($zoneId);

            // Check if zone is actually blocked
            if (!$zone->is_blocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zone is not currently blocked.'
                ], 422);
            }

            // Cancel any active ParkManage records for this zone
            ParkManage::where('zone_id', $zone->id)
                ->where(function($query) {
                    $today = Carbon::today();
                    $now = Carbon::now();
                    
                    // Cancel single blocks that are active or future
                    $query->where('is_recurring', false)
                        ->where(function($q) use ($today, $now) {
                            $q->where('date', '>', $today)
                                ->orWhere(function($subQ) use ($today, $now) {
                                    $subQ->where('date', '=', $today)
                                        ->whereTime('end_time', '>', $now->format('H:i:s'));
                                });
                        });
                })
                ->update(['is_cancelled' => true]);

            // RESET ZONE TO FRESH STATE - Set all counts to 0 and status to empty
            $zone->update([
                'is_blocked' => false,
                'current_status' => self::STATUS_EMPTY, // Reset to empty (new state)
                'status' => 'empty', // Reset status field
                'block_reason' => null,
                'block_date' => null,
                'block_start_time' => null,
                'block_end_time' => null,
                'block_expires_at' => null,
                'status_override' => false,
                'last_reported_at' => null, // Reset last reported time
                'total_reports' => 0, // Reset total reports count to 0
                'total_empty' => 0, // Reset empty count to 0
                'total_half_full' => 0, // Reset half full count to 0
                'total_full' => 0, // Reset full count to 0
            ]);
            
            Report::where('parking_zone_id', $zone->id)->delete();
            
            // Create a new report entry for the unblock action (fresh start)
            Report::create([
                'user_id' => Auth::guard('admin')->id() ?: 1,
                'parking_zone_id' => $zone->id,
                'status' => self::STATUS_EMPTY, // Set to empty (new state)
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log the unblock action
            $admin = Auth::guard('admin')->user();
            
            SystemLog::create([
                'admin_id' => Auth::guard('admin')->id(),
                'description' => "Admin '{$admin->username}' unblocked and reset zone P{$zone->zone_number} to fresh state",
                'action' => 'Unblock & Reset',
                'model' => 'ParkingZone',
            ]);

            DB::commit();

            // Return fresh zone data
            $zone->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Zone unblocked and reset to fresh state successfully.',
                'zone' => [
                    'id' => $zone->id,
                    'number' => $zone->zone_number,
                    'status' => 'empty', // Always return as empty (new state)
                    'current_status' => self::STATUS_EMPTY,
                    'is_blocked' => false,
                    'is_fresh' => true, // Indicate this is a fresh/reset state
                    'zone' => $zone
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock zone: ' . $e->getMessage(),
            ], 500);
        }
    }

    // FIX: Add helper method to determine previous status
    private function determinePreviousStatus($zone)
    {
        // Get the most recent report before the block
        $lastReport = Report::where('parking_zone_id', $zone->id)
            ->where('status', '!=', self::STATUS_BLOCKED)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastReport) {
            return $lastReport->status;
        }

        // Default to empty if no previous status found
        return self::STATUS_EMPTY;
    }

    // FIX: Add helper method to get status label
    private function getStatusLabel($status)
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
                return 'empty';
        }
    }

    public function getFutureBlocks(Request $request)
    {
        $search = $request->get('search', '');
        $filter = $request->get('filter', '');

        $query = ParkManage::with(['zone'])
        ->where('is_cancelled', false)
        ->where(function($q) {
            $now = Carbon::now();
            $q->where('date', '>', $now->toDateString())
            ->orWhere(function($subQ) use ($now) {
                $subQ->where('date', $now->toDateString())
                    ->where('start_time', '>', $now->format('H:i:s'));
            })
            ->orWhere(function($subQ) use ($now) {
                $subQ->where('is_recurring', true)
                    ->where(function($recurQ) use ($now) {
                        $recurQ->whereNull('recurring_end_date')
                                ->orWhere('recurring_end_date', '>=', $now->toDateString());
                    });
            });
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('zone', function($zoneQ) use ($search) {
                      $zoneQ->where('zone_name', 'like', "%{$search}%")
                           ->orWhere('zone_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($filter) {
            $query->whereHas('zone', function($q) use ($filter) {
                $q->where('zone_type', $filter);
            });
        }

        $blocks = $query->orderBy('date', 'asc')
                       ->orderBy('start_time', 'asc')
                       ->get();
        
        Log::info('Future blocks:', $blocks->toArray());
        return response()->json($blocks->map(function($block) {
            $zone = $block->zone;
            return [
                'id' => $block->id,
                'zone_name' => $zone ? ($zone->zone_name ?? "Zone P{$zone->zone_number}") : 'Unknown Zone',
                'zone_number' => $zone ? $zone->zone_number : 'N/A',
                'zone_type' => $zone ? $zone->zone_type : 'N/A',
                'reason' => $block->reason,
                'date' => $block->date,
                'start_time' => $block->start_time,
                'end_time' => $block->end_time,
                'schedule_type' => $block->schedule_type,
                'is_recurring' => $block->is_recurring,
                'weekly_days' => $block->is_recurring ? $block->weekly_days : null,
                'recurring_end_date' => $block->recurring_end_date,
                'status' => $this->getBlockStatus($block),
            ];
        })->values()->all());
    }

   public function getBlockHistory(Request $request)
    {
        try {
            // Validate the request first
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string',
                'period' => 'nullable|integer',
                'status' => 'nullable|in:active,completed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $query = DB::table('park_manage as pm')
                ->join('parking_zones as pz', 'pm.zone_id', '=', 'pz.id')
                ->select(
                    'pm.id',
                    'pm.zone_id',
                    'pm.reason',
                    'pm.date',
                    'pm.start_time',
                    'pm.end_time',
                    'pm.schedule_type',
                    'pm.weekly_days',
                    'pm.is_cancelled',
                    'pm.created_at',
                    'pz.zone_number',
                    'pz.name as zone_name',
                )
                ->orderBy('pm.created_at', 'desc');

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('pz.zone_number', 'like', "%{$search}%")
                    ->orWhere('pm.reason', 'like', "%{$search}%");
                });
            }

            if ($request->filled('period')) {
                $query->where('pm.date', '>=', now()->subDays($request->period)->toDateString());
            }

            $blocks = $query->get()->map(function($block) {
                try {
                    $currentDate = now()->toDateString();
                    $currentTime = now()->format('H:i:s');
                    
                    $status = 'active';
                    if ($block->is_cancelled) {
                        $status = 'cancelled';
                    } elseif ($block->date < $currentDate || 
                            ($block->date == $currentDate && $block->end_time < $currentTime)) {
                        $status = 'completed';
                    }

                    return [
                        'id' => $block->id,
                        'zone_number' => $block->zone_number ?? 'N/A',
                        'zone_name' => $block->name ?? 'Unknown',
                        'reason' => $block->reason ?? '',
                        'date' => $block->date,
                        'start_time' => $block->start_time,
                        'end_time' => $block->end_time,
                        'schedule_type' => $block->schedule_type,
                        'weekly_days' => is_array($block->weekly_days)
                            ? $block->weekly_days
                            : (is_string($block->weekly_days) && strlen($block->weekly_days) > 0
                                ? (json_decode($block->weekly_days, true) ?: [])
                                : []),
                        'status' => $status,
                        'created_at' => $block->created_at,
                        'admin_username' => 'Admin'
                    ];
                } catch (\Exception $e) {
                    Log::error('Error processing block: '.$block->id.' - '.$e->getMessage());
                    return null;
                }
            })->filter(); // Remove any null entries from failed mappings

            if ($request->filled('status')) {
                $blocks = $blocks->where('status', $request->status)->values();
            }

            return response()->json($blocks->values()->all());

        } catch (\Exception $e) {
            Log::error('Block History Error: '.$e->getMessage()."\n".$e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to load block history',
                'debug' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getBlockStats()
    {
        $totalBlocks = ParkManage::where('is_cancelled', false)->count(); // FIX: Exclude cancelled
        
        $activeBlocks = ParkingZone::where('is_blocked', true)->count();
        
        $futureBlocks = ParkManage::where('is_cancelled', false) // FIX: Exclude cancelled
            ->where(function($q) {
                $q->where('date', '>', Carbon::today())
                  ->orWhere(function($subQ) {
                      $subQ->where('is_recurring', true)
                           ->where(function($recurQ) {
                               $recurQ->whereNull('recurring_end_date')
                                      ->orWhere('recurring_end_date', '>=', Carbon::today());
                           });
                  });
            })
            ->count();

        $mostBlockedZone = ParkManage::select('zone_id', DB::raw('count(*) as block_count'))
            ->where('is_cancelled', false) // FIX: Exclude cancelled
            ->groupBy('zone_id')
            ->orderBy('block_count', 'desc')
            ->with('zone')
            ->first();

        return response()->json([
            'total_blocks' => $totalBlocks,
            'active_blocks' => $activeBlocks,
            'future_blocks' => $futureBlocks,
            'most_blocked_zone' => $mostBlockedZone 
                ? "P{$mostBlockedZone->zone->zone_number} ({$mostBlockedZone->block_count} blocks)"
                : 'None',
        ]);
    }

    private function getBlockStatus($block)
    {
        // FIX: Check if block is cancelled first
        if (isset($block->is_cancelled) && $block->is_cancelled) {
            return 'cancelled';
        }

        $now = Carbon::now();
        $blockDate = Carbon::parse($block->date);
        $blockStart = Carbon::parse($block->date . ' ' . $block->start_time);
        $blockEnd = Carbon::parse($block->date . ' ' . $block->end_time);

        if ($block->is_recurring) {
            return 'recurring';
        }

        if ($now > $blockEnd) {
            return 'completed';
        } elseif ($now >= $blockStart && $now <= $blockEnd) {
            return 'active';
        } else {
            return 'future';
        }
    }

    public function autoUnblockExpiredZones()
    {
    $now = Carbon::now();
        
        $expiredBlocks = ParkingZone::where('is_blocked', true)
            ->where(function($query) use ($now) {
                $query->where('block_expires_at', '<=', $now)
                    ->orWhere(function($q) use ($now) {
                        $q->whereNull('block_expires_at')
                        ->where('block_date', '<', $now->toDateString())
                        ->orWhere(function($subQ) use ($now) {
                            $subQ->where('block_date', '=', $now->toDateString())
                                ->whereRaw("CONCAT(block_date, ' ', block_end_time) <= ?", [$now->toDateTimeString()]);
                        });
                    });
            })
            ->get();

        foreach ($expiredBlocks as $zone) {
            // COMPLETELY RESET ZONE TO FRESH STATE
            $zone->update([
                'is_blocked' => false,
                'current_status' => self::STATUS_EMPTY,
                'status' => 'empty', // Make sure both status fields are reset
                'block_reason' => null,
                'block_date' => null,
                'block_start_time' => null,
                'block_end_time' => null,
                'block_expires_at' => null,
                'status_override' => false,
                'last_reported_at' => null,
                'total_reports' => 0,
                'total_empty' => 0,
                'total_half_full' => 0,
                'total_full' => 0,
                // Add any other count fields you might have
                'user_reports_count' => 0, // if this field exists
                'admin_reports_count' => 0, // if this field exists
            ]);

            // DELETE ALL OLD REPORTS for this zone to start fresh
            Report::where('parking_zone_id', $zone->id)->delete();

            // Create a fresh report with empty status
            Report::create([
                'user_id' => 1, // System user
                'parking_zone_id' => $zone->id,
                'status' => self::STATUS_EMPTY,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            SystemLog::create([
                'admin_id' => null,
                'description' => "Auto-unblocked and reset expired zone P{$zone->zone_number} to fresh state",
                'action' => 'Auto-Unblock & Reset',
                'model' => 'ParkingZone',
            ]);
        }

        return $expiredBlocks->count();
    }

    public function triggerAutoUnblock()
    {
        $count = $this->autoUnblockExpiredZones();
        
        return response()->json([
            'success' => true,
            'message' => "Auto-unblocked {$count} expired zones.",
            'unblocked_count' => $count
        ]);
    }

    public function processRecurringBlocks()
    {
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;

        $recurringBlocks = ParkManage::where('is_recurring', true)
            ->where('is_cancelled', false) // FIX: Only process non-cancelled blocks
            ->where('recurring_start_date', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('recurring_end_date')
                  ->orWhere('recurring_end_date', '>=', $today);
            })
            ->whereJsonContains('weekly_days', $todayDayOfWeek)
            ->get();

        foreach ($recurringBlocks as $block) {
            $zone = ParkingZone::find($block->zone_id);
            
            if (!$zone || $zone->is_blocked) {
                continue;
            }

            $blockExpiration = Carbon::parse($today->format('Y-m-d') . ' ' . $block->end_time);
            
            $zone->update([
                'is_blocked' => true,
                'current_status' => self::STATUS_BLOCKED,
                'status' => 'blocked', // FIX: Set proper status
                'block_reason' => $block->reason,
                'block_date' => $today->format('Y-m-d'),
                'block_start_time' => $block->start_time,
                'block_end_time' => $block->end_time,
                'block_expires_at' => $blockExpiration,
                'status_override' => false, // FIX: Clear any previous override
            ]);

            $this->createReport($zone);

            SystemLog::create([
                'admin_id' => null,
                'description' => "Auto-blocked zone P{$zone->zone_number} due to recurring schedule",
                'action' => 'Auto-Block',
                'model' => 'ParkingZone',
            ]);
        }

        return $recurringBlocks->count();
    }

    public function processScheduledBlocks()
    {
        $now = Carbon::now();
        $blockedCount = 0;

        // Look for blocks that should start within the next 60 seconds
        $upcomingBlocks = ParkManage::where('is_recurring', false)
            ->where('is_cancelled', false)
            ->get()
            ->filter(function($block) use ($now) {
                $date = is_string($block->date) ? substr($block->date, 0, 10) : $block->date->format('Y-m-d');
                $blockStart = Carbon::parse($date . ' ' . $block->start_time);
                $blockEnd = Carbon::parse($date . ' ' . $block->end_time);
                return ($now->between($blockStart, $blockEnd)) || 
                    ($blockStart->gt($now) && $blockStart->lte($now->copy()->addSeconds(60)));
            });

        foreach ($upcomingBlocks as $block) {
            $zone = ParkingZone::find($block->zone_id);
            
            if (!$zone) {
                continue;
            }

            $date = is_string($block->date) ? substr($block->date, 0, 10) : $block->date->format('Y-m-d');
            $blockStart = Carbon::parse($date . ' ' . $block->start_time);
            $blockEnd = Carbon::parse($date . ' ' . $block->end_time);

            // Only activate if it's time AND zone isn't already blocked
            if ($now->gte($blockStart) && $now->lte($blockEnd) && !$zone->is_blocked) {
                $zone->update([
                    'is_blocked' => true,
                    'current_status' => self::STATUS_BLOCKED,
                    'status' => 'blocked',
                    'block_reason' => $block->reason,
                    'block_date' => $block->date,
                    'block_start_time' => $block->start_time,
                    'block_end_time' => $block->end_time,
                    'block_expires_at' => $blockEnd,
                    'status_override' => false,
                ]);

                $this->createReport($zone);

                SystemLog::create([
                    'admin_id' => null,
                    'description' => "Auto-activated scheduled block for zone P{$zone->zone_number}: {$block->reason}",
                    'action' => 'Auto-Block (Scheduled)',
                    'model' => 'ParkingZone',
                ]);

                $blockedCount++;
            }
        }
            return $blockedCount;
    }


    public function cancelBlock($blockId)
    {
        try {
            DB::beginTransaction();

            $block = ParkManage::findOrFail($blockId);
            
            // Mark the block as cancelled
            $block->update(['is_cancelled' => true]);
            
            // If this block is currently active on the zone, unblock it
            $zone = ParkingZone::find($block->zone_id);
            if ($zone && $zone->is_blocked && 
                $zone->block_date === $block->date &&
                $zone->block_start_time === $block->start_time &&
                $zone->block_end_time === $block->end_time) {
                
                // Get previous status before unblocking
                $previousStatus = $this->determinePreviousStatus($zone);
                
                $zone->update([
                    'is_blocked' => false,
                    'current_status' => $previousStatus,
                    'block_reason' => null,
                    'block_date' => null,
                    'block_start_time' => null,
                    'block_end_time' => null,
                    'block_expires_at' => null,
                    'status_override' => false,
                ]);
                
                // Create report for the unblock
                Report::create([
                    'user_id' => Auth::guard('admin')->id() ?: 1,
                    'parking_zone_id' => $zone->id,
                    'status' => $previousStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Log the cancellation
            $admin = Auth::guard('admin')->user();
            
            SystemLog::create([
                'admin_id' => Auth::guard('admin')->id(),
                'description' => "Admin '{$admin->username}' cancelled block for zone P{$zone->zone_number}: {$block->reason}",
                'action' => 'Cancel Block',
                'model' => 'ParkManage',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Block cancelled successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel block: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkAndProcessExpiredBlocks()
    {
        $now = Carbon::now();
        
        // Get all blocked zones
        $blockedZones = ParkingZone::where('is_blocked', true)->get();
        
        $expiredCount = 0;
        
        foreach ($blockedZones as $zone) {
            $isExpired = false;
            
            // Check if block_expires_at is set and expired
            if ($zone->block_expires_at && Carbon::parse($zone->block_expires_at)->isPast()) {
                $isExpired = true;
            }
            // Check if block date and time combination is expired
            elseif ($zone->block_date && $zone->block_end_time) {
                $blockEndDateTime = Carbon::parse($zone->block_date . ' ' . $zone->block_end_time);
                if ($blockEndDateTime->isPast()) {
                    $isExpired = true;
                }
            }
            
            if ($isExpired) {
                $this->resetZoneToFreshState($zone);
                $expiredCount++;
            }
        }
        
        return $expiredCount;
    }

    private function resetZoneToFreshState($zone)
    {
        // COMPLETELY RESET ZONE
        $zone->update([
            'is_blocked' => false,
            'current_status' => self::STATUS_EMPTY,
            'status' => 'empty',
            'block_reason' => null,
            'block_date' => null,
            'block_start_time' => null,
            'block_end_time' => null,
            'block_expires_at' => null,
            'status_override' => false,
            'last_reported_at' => null,
            'total_reports' => 0,
            'total_empty' => 0,
            'total_half_full' => 0,
            'total_full' => 0,
        ]);

        // DELETE ALL OLD REPORTS
        Report::where('parking_zone_id', $zone->id)->delete();

        // Create fresh empty report
        Report::create([
            'user_id' => 1,
            'parking_zone_id' => $zone->id,
            'status' => self::STATUS_EMPTY,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SystemLog::create([
            'admin_id' => null,
            'description' => "Auto-reset expired zone P{$zone->zone_number} to fresh empty state",
            'action' => 'Auto-Reset',
            'model' => 'ParkingZone',
        ]);
    }
}