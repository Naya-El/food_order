<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Intervention\Image\Drivers\AbstractDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $data = $request->validate([
            'username'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'phone_number'    => 'nullable|string|max:30|unique:users,phone_number',
            'password' => 'required|confirmed|min:8',
        ]);

        if (!$request->filled('email') && !$request->filled('phone_number')) {
            return response()->json([
                'message' => 'يجب إدخال البريد الإلكتروني أو رقم الهاتف'
            ], 422);
        }

        if ($request->hasFile('profile_image')) {
            $manager = new ImageManager(new Driver());

            $extension = $request->file('profile_image')->getClientOriginalExtension();
            $profileImagePath = rand(111111, 999999) . '.' . $extension;
            $savePath = storage_path('app/public/images/' . $profileImagePath);

            if (!file_exists(dirname($savePath))) {
                mkdir(dirname($savePath), 0755, true);
            }

            $img = $manager->read($request->file('profile_image'));
            $img->resize(500, 500)->save($savePath);
        } else {
            $profileImagePath = "";
        }



        $user = new User();
        $user->username = $request['username'];
        $user->email = $request['email'];
        $user->password = Hash::make($data['password']);
        $user->phone_number = $request['phone_number'];
        $user->role = 'customer';
        $user->is_active = 1;
        $user->points = 0;
        $user->profile_image = $profileImagePath;
        $user->save();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success'=>true,
            'user'    => $user,
            'token'   => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'nullable|email',
            'phone_number' => 'nullable|string',
            'password' => 'required|string',
        ]);


        if (!$request->filled('email') && !$request->filled('phone_number')) {
            return response()->json([
                'message' => 'أدخل البريد الإلكتروني أو رقم الهاتف مع كلمة المرور'
            ], 422);
        }

        if ($request['email']!=="") {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone_number', $request->phone_number)->first();
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'user'    => $user,
            'token'   => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true]);
    }
}
