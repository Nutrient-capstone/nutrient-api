<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function store(Request $request)
    {
        // return $request;
        //     $validate = Validator::make($request->all(), [
        //         "name" => "required|string|max:255|unique:classrooms,name",
        //         "name_alias" => "nullable|string|max:255",
        //         "photo" => "nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048",
        //         "pic_room_id" => "required|integer|exists:pic_rooms,id",
        //         "building_id" => "required|integer|exists:buildings,id",
        //     ]);
        //     if ($validate->fails()) {
        //         return response()->json([
        //             'status' => 400,
        //             'message' => $validate->errors()
        //         ], 400);
        //     } else {
        //         $data = $validate->validated();
        //         try {
        //             $uploadFolder = 'classroom-photo';
        //             $photo = $request->file('photo');
        //             $image_uploaded_path = $photo->store($uploadFolder, 'public');
        //             $data['photo'] = Storage::disk('public')->url($image_uploaded_path);
        //             $classroom = classroom::create($data);
        //             return response()->json([
        //                 'status' => 201,
        //                 'message' => 'Data Added Successfully',
        //                 'data' => new classroomResource($classroom)
        //             ], 201);
        //         } catch (\Throwable $th) {
        //             return response()->json([
        //                 'status' => 500,
        //                 'message' => $th->getMessage(),
        //             ], 500);
        //         }
        //     }
    }
}
