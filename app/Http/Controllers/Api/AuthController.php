<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AppUserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Đăng nhập API qua Zalo
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            // 'access_token' => 'required|string',
            // 'code' => 'required|string',
            'secret_key' => 'required|string',
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $serverSecretKey = env('SECRET_KEY_ZALO');

        if ($request->secret_key !== $serverSecretKey) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid secret key',
            ], 401);
        }

        // Tìm user theo số điện thoại
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            // Tạo user mới
            $user = User::create([
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('end_user');

            // Tạo profile trong app
            AppUserProfile::create([
                'user_id' => $user->id,
                'app_id' => $request->header('X-App-Id'),
                'name' => $request->name,
                'active' => true,
            ]);
        }

        // Tạo token cho user
        $token = $user->createToken('api-token')->plainTextToken;

        // Lấy thông tin profile của user
        $profile = AppUserProfile::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $profile,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Đăng nhập API truyền thống (backup)
     */
    public function loginWithPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Thông tin đăng nhập không chính xác'
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // Lấy thông tin profile của user
        $profile = AppUserProfile::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $profile,
                'token' => $token
            ]
        ]);
    }
}
