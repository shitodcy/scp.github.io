<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class DashboardController extends Controller
{

    public function index()
    {

        $userCount = User::count();
        $dbStatus = 'Online';
        $dbStatusClass = 'bg-success';

        try {

            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'Offline';
            $dbStatusClass = 'bg-danger';
        }


        return view('layouts.dashboard', [
        'userCount' => $userCount,
        'dbStatus' => $dbStatus,
        'dbStatusClass' => $dbStatus === 'Online' ? 'bg-success' : 'bg-danger',
    ]);
    }
}
