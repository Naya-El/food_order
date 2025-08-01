<?php

namespace App\Http\Controllers\CustomerController;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone_number' => ['required', 'regex:/^[0-9]{10}$/'],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $profileImage = "";

        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');

            if ($image->isValid()) {
                $extension = $image->getClientOriginalExtension();
                $profileImage = uniqid('profile_') . '.' . $extension;

                $folderPath = public_path('images');

                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }

                $path = $folderPath . '/' . $profileImage;

                $imageManager = new ImageManager(new Driver());

                $imageManager->read($image)
                    ->resize(1000, 1000)
                    ->save($path);
            }
        }
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'],
            'is_active' => true,
            'points' => 0,
            'profile_image'=>$profileImage
        ]);

        $token = $user->createToken('flutter_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Sorry, your email or password is incorrect. Please try again.'],
            ]);
        }

        $token = $user->createToken('flutter_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
