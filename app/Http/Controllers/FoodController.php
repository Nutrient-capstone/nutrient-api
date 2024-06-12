<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\DailyIntake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $food = Food::where('user_id', $user->id)->orderBy('id', 'desc')->get();
            return response()->json([
                'status' => 200,
                'data' => $food
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // return $request;
        $id = Auth::user()->id;
        $user = Auth::user();
        $validate = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "calories" => "required|integer",
            "sugar" => "required|integer",
            "fat" => "required|integer",
            "protein" => "required|integer",
            "carbohydrate" => "required|integer",
            "image" => "nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048",
            // "comsumpsition_time" => "required|integer|max:1",
            // "user_id" => $id
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()
            ], 400);
        } else {
            $data = $validate->validated();
            $data['image'] = '';
            $data['user_id'] = $user->id;
            try {
                DB::beginTransaction();
                // return [$data, 'user_id' => $id];
                if ($request->hasFile('image')) {
                    $uploadFolder = 'food-image';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $data['image'] = Storage::disk('public')->url($image_uploaded_path);
                }
                // add food
                $food = Food::create($data);
                // update table daily intake
                $dailyIntake = DailyIntake::where('user_id', $user->id)->latest()->first();
                $dailyIntake->update([
                    "daily_calories" => $dailyIntake['daily_calories'] + $data['calories'],
                    "daily_sugar" => $dailyIntake['daily_sugar'] + $data['sugar'],
                    "daily_fat" => $dailyIntake['daily_fat'] + $data['fat'],
                    "daily_protein" => $dailyIntake['daily_protein'] + $data['protein'],
                    "daily_carbohydrate" => $dailyIntake['daily_carbohydrate'] + $data['carbohydrate'],
                ]);
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Data Added Successfully',
                    'data' => $food
                ], 201);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 500,
                    'message' => $th->getMessage(),
                ], 500);
            }
        }
    }
}
