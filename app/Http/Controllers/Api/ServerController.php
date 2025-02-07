<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServerController extends Controller
{
    public function index()
    {
        $servers = Server::all();

        return response()->json([
            'status' => true,
            'servers' => $servers,
        ], 200);
    }

    public function plans()
    {
        // Fetch all plans except the one with ID 1
        $plans = Plan::all(); // Exclude plan with ID 1

        return response()->json([
            'status' => true,
            'plans' => $plans
        ], 200);
    }
}
