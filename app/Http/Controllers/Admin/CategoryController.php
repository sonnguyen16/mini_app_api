<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\App;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách categories
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;
        $app = null;

        // Nếu là app_owner, chỉ xem policies của app mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $appId = $app ? $app->id : null;
        }

        $categories = Category::with(['app', 'vouchers'])
            ->when($appId, function ($query, $appId) {
                return $query->where('app_id', $appId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->active !== null, function ($query) use ($request) {
                return $query->where('active', $request->active);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $apps = $user->hasRole('admin') ? App::active()->get() : collect([$app]);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'apps' => $apps,
            'filters' => $request->only(['search', 'active', 'app_id']),
            'currentAppId' => $appId,
        ]);
    }

    /**
     * Hiển thị form tạo category mới
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $apps = $user->hasRole('admin') ? App::active()->get() :
                App::where('owner_email', $user->email)->active()->get();

        return Inertia::render('Categories/Create', [
            'apps' => $apps,
        ]);
    }

    /**
     * Lưu category mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'app_id' => 'required|exists:apps,id',
        ]);

        Category::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'app_id' => $request->app_id,
            'active' => true,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category đã được tạo thành công');
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit(Category $category)
    {
        $user = request()->user();
        $apps = $user->hasRole('admin') ? App::active()->get() :
                App::where('owner_email', $user->email)->active()->get();

        return Inertia::render('Categories/Edit', [
            'category' => $category->load('app', 'vouchers'),
            'apps' => $apps,
        ]);
    }

    /**
     * Cập nhật category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $category->update($request->only(['name', 'icon', 'active']));

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category đã được cập nhật');
    }

    /**
     * Xóa category
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category đã được xóa');
    }
}
