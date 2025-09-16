<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\App;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    /**
     * Hiển thị danh sách apps (chỉ admin)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', App::class);

        $apps = App::with(['userProfiles', 'vouchers', 'userProfiles', 'owner'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->active !== null, function ($query) use ($request) {
                return $query->where('active', $request->active);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Apps/Index', [
            'apps' => $apps,
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    /**
     * Hiển thị form tạo app mới
     */
    public function create()
    {
        $this->authorize('create', App::class);

        return Inertia::render('Apps/Create');
    }

    /**
     * Lưu app mới
     */
    public function store(Request $request)
    {
        $this->authorize('create', App::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'owner_email' => 'required|email|unique:users,email|unique:apps,owner_email',
            'owner_password' => 'required|string|min:6',
            'mini_app_id' => 'nullable|string|unique:apps,mini_app_id',
            "owner_name" => "required|string|max:255",
        ]);

        // Xử lý upload logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('apps/logos', 'public');
        }

        // Tạo app
        $app = App::create([
            'name' => $request->name,
            'description' => $request->description,
            'logo' => $logoPath,
            'owner_email' => $request->owner_email,
            'owner_password_hash' => Hash::make($request->owner_password),
            'mini_app_id' => $request->mini_app_id,
            'active' => true,
            'owner_name' => $request->owner_name,
        ]);

        // Tạo user app_owner
        if (!User::where('email', $request->owner_email)->exists()) {
            $owner = User::create([
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
            ]);
            $owner->assignRole('app_owner');
        }

        return redirect()->route('admin.apps.index')
            ->with('success', 'App đã được tạo thành công');
    }

    /**
     * Hiển thị chi tiết app
     */
    public function show(App $app)
    {
        $this->authorize('view', $app);

        $app->load(['userProfiles.user', 'categories', 'vouchers', 'owner']);

        return Inertia::render('Apps/Show', [
            'app' => $app,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit(App $app)
    {
        $this->authorize('update', $app);

        $app->load(['userProfiles', 'categories', 'vouchers']);

        return Inertia::render('Apps/Edit', [
            'app' => $app,
        ]);
    }

    /**
     * Cập nhật app
     */
    public function update(Request $request, App $app)
    {
        $this->authorize('update', $app);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'mini_app_id' => ['nullable', 'string', Rule::unique('apps', 'mini_app_id')->ignore($app->id)],
            'active' => 'boolean',
            'owner_name' => 'required|string|max:255',
        ]);

        // Xử lý upload logo
        $updateData = $request->only(['name', 'description', 'mini_app_id', 'active', 'owner_name']);

        if ($request->hasFile('logo')) {
            // Xóa logo cũ nếu có
            if ($app->logo && Storage::disk('public')->exists($app->logo)) {
                Storage::disk('public')->delete($app->logo);
            }
            $updateData['logo'] = $request->file('logo')->store('apps/logos', 'public');
        }

        $app->update($updateData);

        return redirect()->route('admin.apps.index')
            ->with('success', 'App đã được cập nhật');
    }

    /**
     * Reset password cho app owner
     */
    public function resetPassword(Request $request, App $app)
    {
        $this->authorize('update', $app);

        $app->update([
            'owner_password_hash' => Hash::make('123456'),
        ]);

        // Cập nhật password cho user
        $owner = User::where('email', $app->owner_email)->first();
        if ($owner) {
            $owner->update([
                'password' => Hash::make('123456'),
            ]);
        }

        return redirect()->back()->with('success', 'Mật khẩu đã được reset');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(App $app)
    {
        $app->update([
            'active' => !$app->active,
        ]);

        return back()->with('success', 'Trạng thái đã được thay đổi');
    }

    /**
     * Xóa app
     */
    public function destroy(App $app)
    {
        $this->authorize('delete', $app);

        // Xóa user owner
        $owner = User::where('email', $app->owner_email)->first();
        if ($owner) {
            $owner->delete();
        }

        $app->delete();

        return redirect()->route('admin.apps.index')
            ->with('success', 'App đã được xóa');
    }
}
