<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $food = Food::where('user_id', $user->id)->get();
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
                // return [$data, 'user_id' => $id];
                if ($request->hasFile('image')) {
                    $uploadFolder = 'food-image';
                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $data['image'] = Storage::disk('public')->url($image_uploaded_path);
                }
                $food = Food::create($data);
                return response()->json([
                    'status' => 201,
                    'message' => 'Data Added Successfully',
                    'data' => $food
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 500,
                    'message' => $th->getMessage(),
                ], 500);
            }
        }
    }
}
