<?php

namespace App\Http\Controllers;

use App\Models\DailyIntake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyIntakeController extends Controller
{
    public function get()
    {
        try {
            $user = Auth::user();
            $dailyIntake = DailyIntake::where('user_id', $user->id)->latest()->first();
            return response()->json([
                'status' => 200,
                'data' => $dailyIntake
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
