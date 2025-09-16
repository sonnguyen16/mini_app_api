<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppPolicy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Lấy chính sách thành viên
     */
    public function membership(Request $request)
    {
        $appId = $request->app_id;

        $policy = AppPolicy::forApp($appId)
                          ->byType('membership_policy')
                          ->first();

        return response()->json([
            'success' => true,
            'data' => $policy ? $policy->content : null
        ]);
    }

    /**
     * Lấy chính sách bảo mật
     */
    public function privacy(Request $request)
    {
        $appId = $request->app_id;

        $policy = AppPolicy::forApp($appId)
                          ->byType('privacy_policy')
                          ->first();

        return response()->json([
            'success' => true,
            'data' => $policy ? $policy->content : null
        ]);
    }
}
