<?php

namespace App\Http\Controllers;

use App\Models\Bmi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BmiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        try {
            $user = Auth::user();
            $bmi = Bmi::where('user_id', $user->id)->get();
            // return $user;
            return response()->json([
                'status' => 200,
                'data' => $bmi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
