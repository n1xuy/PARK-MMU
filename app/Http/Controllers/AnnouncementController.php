<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement; // Add this line

class AnnouncementController extends Controller
{   
    public function handleAnnouncement()
    {   
        $announcement = Announcement::latest()->first() ?? new Announcement;
        return view('announcement-edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'details' => 'required|string'
        ]);

        $announcement->update($validated);

        $admin = Auth::guard('admin')->user();

        SystemLog::create([
            'admin_id' => $admin->id,
            'description' => "Admin '{$admin->username}' updated the annoucement: {$announcement->title}",
            'action' => 'Update',
            'model' => 'Announcement',
        ]);

        return redirect()->route('admin.announce')
            ->with('success', 'Announcement updated successfully!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'details' => 'required|string',
        ]);

        $validated['admin_id'] = auth('admin')->id();
        
        $announcement = Announcement::create($validated);

        $admin = Auth::guard('admin')->user();

        SystemLog::create([
            'admin_id' => $admin->id,
            'description' => "Admin '{$admin->username}' created new announcement: {$announcement->title}",
            'action' => 'Create',
            'model' => 'Announcement',
        ]);

        $logs = SystemLog::with('admin')->latest()->get();

        return redirect()
            ->route('admin.announce')
            ->with('success', 'Announcement created successfully!');
    }

    public function clear(Announcement $announcement)
    {
        $announcement->update([
            'title' => 'Welcome to ParkMMU!',
            'date' => null,
            'time' => null,
            'details' => '',
        ]);

        $admin = Auth::guard('admin')->user();

        SystemLog::create([
            'admin_id' => $admin->id,
            'description' => "Admin '{$admin->username}' delete the annoucement: {$announcement->title}",
            'action' => 'Delete',
            'model' => 'Announcement',
        ]);

        return redirect()->route('admin.announce')
            ->with('success', 'Announcement cleared successfully!');
    }

}