<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemLog;

class SystemLogController extends Controller
{
    public function index(){

        $logs = SystemLog::orderBy('created_at', 'desc')->get();

        return view('systemlogs',compact('logs'));
    }
}
