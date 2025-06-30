<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportDataController extends Controller
{
        public function index()
    {
        $reports = Report::orderBy('created_at', 'desc')->get();
        $reports = Report::with('parkingZone')->get();
        return view('reportdata', compact('reports'));
    }
}
