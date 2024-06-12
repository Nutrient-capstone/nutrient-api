<?php

namespace App\Http\Controllers;

use Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AccountCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\MyProfileResources;
use App\Models\DailyIntake;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function myprofile(Request $request)
    {
        $user = Auth::user();
        $data = $user->userData()->first();

        return response()->json([
            'status' => 200,
            'data' => new MyProfileResources($data)
        ]);
    }

    public function updateAssesment(Request $request)
    {
        $user = Auth::user();
        $gender = $user->userData->gender;
        $age = Carbon::parse($user->userData->birthdate)->age;

        // return $gender;

        $validatedData = $request->validate([
            'weight' => "required|decimal:0,2|gt:1",
            'height' => "required|decimal:0,2|gt:1"
        ]);

        try {
            DB::beginTransaction();
            $user->update([
                'new_user' => false
            ]);

            $user->userData()->update([
                'weight' => $validatedData['weight'],
                'height' => $validatedData['height']
            ]);

            $resultBmi = $this->countBmi($validatedData['weight'], $validatedData['height']);
            $user->bmi()->create([
                "height" => $validatedData['height'],
                "weight" => $validatedData['weight'],
                "bmi" => $resultBmi['bmi'],
                "status" => $resultBmi['status'],
                "user_id" => $user->id
            ]);

            $resultBmr = $this->countBmr($gender, $age, $validatedData['weight'], $validatedData['height']);
            $user->dailyIntake()->create([
                "max_calories" => $resultBmr,
                "user_id" => $user->id
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Update successfull',
            ], 200);
        } catch (\Throwable $th) {

            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function userStatus(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'status' => 200,
            'new_user' => $user->new_user,
        ], 200);
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'min:8|required',
            'password_confirmation' => 'same:password',
            'birthdate' => 'required|date',
            'gender' => 'required|integer|max:1'
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()
            ], 400);
        } else {
            $data = $validate->validated();
            try {
                $user = User::create([
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ]);
                UserData::create([
                    'birthdate' => $data['birthdate'],
                    'gender' => $data['gender'],
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'User Added Successfully',
                    'data' => $user,
                ], 201);
            } catch (Throwable $th) {
                return response()->json([
                    'status' => 500,
                    'message' => $th->getMessage(),
                ], 500);
            }
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token =  $user->createToken('user login')->plainTextToken;

        return response()->json([
            'type' => 'Bearer',
            'token' => $token,
            'data' => [
                'username' => $user->username,
                'email' => $user->email
            ],
            'new_user' => $user->new_user,
        ]);
    }

    public function get()
    {
        try {
            $id = Auth::user()->id;
            $user = User::with('userData')->findOrFail($id);
            // return $user;
            return response()->json([
                'status' => 200,
                'data' => new AccountCollection($user)
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getBmi()
    {
        try {
            $user = Auth::user();
            // $user = User::findOrFail($id)->join('user_data', 'users.id', '=', 'user_data.id')->first();
            // return $user;
            $height = $user->userData->height;
            $weight = $user->userData->weight;
            $resultBmi = $this->countBmi($weight, $height);
            return response()->json([
                'status' => 200,
                'data' => [
                    'bmi' => $resultBmi['bmi'],
                    'status' => $resultBmi['status']
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $id = Auth::user()->id;
        $validate = Validator::make($request->all(), [
            "password" => "required|min:8|confirmed",
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()
            ], 400);
        } else {
            $data = $validate->validated();
            try {
                $user = User::findOrFail($id);
                // Update only if there is a change in the user_code or name
                $user->update([
                    // 'username' => $data['username'],
                    'password' => Hash::make($data['password']),
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'User Data Updated Successfully',
                    'data' => $user
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 500,
                    'message' => $th->getMessage(),
                ], 500);
            }
        }
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;
        $validate = Validator::make($request->all(), [
            // "email" => "required|string|unique:users,email,$id,id",
            "username" => "required|string|max:100|unique:users,username,$id,id",
            "birthdate" => "required|date",
            "gender" => "required|integer|max:1",
            "height" => "required|decimal:0,2|gt:1",
            "weight" => "required|decimal:0,2|gt:1",
            "image" => "nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048",
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()
            ], 400);
        } else {
            $data = $validate->validated();
            try {
                DB::beginTransaction();
                $user = User::findOrFail($id);
                // return DailyIntake::where('user_id', $user->id)->latest()->first();
                $userData = UserData::where('user_id', $id)->firstOrFail();
                // Update only if there is a change in the column except photo
                $user->update([
                    'username' => $data['username'],
                ]);
                $userData->update([
                    "birthdate" => $data['birthdate'],
                    "gender" => $data['gender'],
                    "height" => $data['height'],
                    "weight" => $data['weight'],
                ]);
                if ($userData->wasChanged('height') || $userData->wasChanged('height') || $userData->wasChanged('birthdate')) {
                    $dailyIntake = DailyIntake::where('user_id', $user->id)->latest()->first();
                    $resultBmi = $this->countBmi($data['weight'], $data['height']);
                    $user->bmi()->create([
                        "height" => $data['height'],
                        "weight" => $data['weight'],
                        "bmi" => $resultBmi['bmi'],
                        "status" => $resultBmi['status'],
                        "user_id" => $user->id
                    ]);
                    $gender = intval($userData->gender);
                    $age = Carbon::parse($userData->birthdate)->age;
                    $resultBmr = $this->countBmr($gender, $age, $data['weight'], $data['height']);
                    $dailyIntake->update([
                        "max_calories" => $resultBmr,
                    ]);
                }
                // Update the photo if a new one is provided
                if ($request->hasFile('image')) {
                    $uploadFolder = 'profile-picture';
                    // Delete the old photo if it exists
                    if ($userData->image) {
                        $photoPath = basename($userData->image);
                        $storagePath = 'profile-picture/' . $photoPath;
                        // Check if the file exists before attempting to delete
                        if (Storage::disk('public')->exists($storagePath)) {
                            // Delete the file from storage
                            Storage::disk('public')->delete($storagePath);
                        } else {
                            \Log::info('File does not exist: ' . $storagePath);
                        }
                    }

                    $image = $request->file('image');
                    $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $userData->update(['image' => Storage::disk('public')->url($image_uploaded_path)]);
                }
                $user = User::with('userData')->findOrFail($id);
                DB::commit();
                return response()->json([
                    'status' => 201,
                    'message' => 'Data Updated Successfully',
                    'data' => new AccountCollection($user)
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

    public function logout(Request $request)
    {
        return $request->user()->currentAccessToken()->delete();
    }

    private function countBmi(Float $weight,  Float $height)
    {
        $bmi = $weight / (($height / 100) * ($height / 100));
        // $bmi = ($height / 100) * ($height / 100);
        // $bmi = 70 / 3.08;

        if ($bmi < 18.5) {
            $status = "Underweight";
        } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
            $status = "Normal weight";
        } elseif ($bmi >= 25 && $bmi <= 29.9) {
            $status = "Overweight";
        } else {
            $status = "Obesity";
        }

        return [
            "bmi" => $bmi,
            "status" => $status
        ];
    }

    private function countBmr(Int $gender, Int $age, Float $weight,  Float $height)
    {

        if ($gender) { // jika gender adalah lanang
            $bmr = (66.5 + (13.75 * $weight) + (5.003 * $height) - (6.75 * $age)) * 1.3;
        } else { // jika gender adalah wedok
            $bmr = (655.1 + (9.563 * $weight) + (1.850 * $height) - (4.676 * $age)) * 1.3;
        }

        return $bmr;
    }

    public function getBmr()
    {
        return $this->countBmr(1, 23, 67, 175);
    }

    public function checkAuth()
    {
        return Auth::user();
    }
}
