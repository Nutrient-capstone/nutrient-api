<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use App\Models\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        ]);
    }
}
