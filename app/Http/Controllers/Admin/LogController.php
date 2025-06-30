<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{

    public function index()
    {

        $activities = Activity::latest()->paginate(20);

        return view('admin.logs.index', compact('activities'));
    }


    public function clear()
    {

        Activity::truncate();


        activity()->log('All activity logs were cleared.');

        return redirect()->route('admin.logs.index')->with('success', 'Semua log aktivitas berhasil dihapus.');
    }
}
