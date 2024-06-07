<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountCollection;
use Log;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
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
        ]);
    }

    public function get()
    {
        try {
            $id = Auth::user()->id;
            $user = User::findOrFail($id)->join('user_data', 'users.id', '=', 'user_data.id')->get();
            // return $user;
            return response()->json([
                'status' => 200,
                // 'data' => $user
                'data' => AccountCollection::collection($user)
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
            $id = Auth::user()->id;
            $user = User::findOrFail($id)->join('user_data', 'users.id', '=', 'user_data.id')->first();
            // return $user;
            $height = $user->height;
            $weight = $user->weight;
            $bmi = $weight / (($height / 100) * 2);
            return response()->json([
                'status' => 200,
                'data' => [
                    'bmi' => $bmi,
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
            "height" => "required|decimal:0,2",
            "weight" => "required|decimal:0,2",
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
                $user = User::findOrFail($id);
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
                // return $userData;
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
                $user = User::findOrFail($id)->join('user_data', 'users.id', '=', 'user_data.id')->get();
                return response()->json([
                    'status' => 201,
                    'message' => 'Data Updated Successfully',
                    'data' => AccountCollection::collection($user)
                ], 201);
            } catch (\Throwable $th) {
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
}
