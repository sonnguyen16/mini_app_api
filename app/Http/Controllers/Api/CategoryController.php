<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Lấy danh sách categories
     */
    public function index(Request $request)
    {
        $appId = $request->app_id;
        $active = $request->query('active', 1);

        $categories = Category::forApp($appId)
                             ->when($active, function ($query) {
                                 return $query->active();
                             })
                             ->orderBy('name')
                             ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
