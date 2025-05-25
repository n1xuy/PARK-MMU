<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function update(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'date' => 'required|date',
        'time' => 'required',
        'details' => 'required|string'
    ]);

    $datetime = $validated['date'] . ' ' . $validated['time'];
    
    $announcement = Announcement::updateOrCreate(
        ['id' => 1], // Assuming you only keep one announcement
        [
            'title' => $validated['title'],
            'details' => $validated['details'],
            'is_active' => true,
            'created_at' => $datetime,
            'updated_at' => now()
        ]
    );
    
    return response()->json([
        'success' => true,
        'announcement' => [
            'title' => $announcement->title,
            'details' => $announcement->details,
            'time' => $announcement->created_at->format('d M Y, h:i A')
        ]
    ]);
}

    public function edit()
{

    $announcement = Announcement::active()->latest()->first(); // scope
    
    return view('announcementedit', compact('announcement'));
}

}
