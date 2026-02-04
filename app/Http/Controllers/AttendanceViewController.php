<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceViewController extends Controller
{
    /**
     * Show the tablet time clock interface.
     */
    public function tablet()
    {
        return view('attendance.tablet');
    }
}
