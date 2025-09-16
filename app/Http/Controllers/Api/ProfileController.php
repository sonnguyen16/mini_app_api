<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppUserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Lấy thông tin profile của user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;

        $profile = AppUserProfile::where('user_id', $user->id)
                                ->where('app_id', $appId)
                                ->with(['user', 'app'])
                                ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'error' => 'Profile not found in this app'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Cập nhật profile
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'birthday' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'address' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $appId = $request->app_id;

        $profile = AppUserProfile::where('user_id', $user->id)
                                ->where('app_id', $appId)
                                ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'error' => 'Profile not found in this app'
            ], 404);
        }

        $profile->update($request->only(['name', 'birthday', 'gender', 'address']));

        return response()->json([
            'success' => true,
            'data' => $profile->fresh()
        ]);
    }
}
