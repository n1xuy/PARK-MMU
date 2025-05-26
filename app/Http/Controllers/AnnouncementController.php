<?php

namespace App\Http\Controllers;

use App\Models\Announcement; // Add this line
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function create()
    {
        return view('announcement-edit');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'details' => 'required'
        ]);

        $announcement->update($validated);

        return redirect()->route('admin.announce')->with('success', 'Announcement updated successfully!');
    }

    public function edit(Announcement $announcement)
    {       
        return view ('announcement-edit', compact('announcement'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'details' => 'required|string',
        ]);

        Announcement::create($validated);

        return redirect()->route('admin.announce')->with('success', 'Announcement created.');
    }

    public function clear($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update([
            'title' => '',
            'date' => null,
            'time' => null,
            'details' => '',
        ]);

        return redirect()->back()->with('cleared', 'Announcement has been cleared.');
    }

}