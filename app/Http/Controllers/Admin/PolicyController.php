<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AppPolicy;
use App\Models\App;

class PolicyController extends Controller
{
    /**
     * Hiển thị form tạo policy mới
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $apps = $user->hasRole('admin') ? App::active()->get() : [];

        // Nếu là app_owner, chỉ có thể tạo policy cho app của mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $apps = $app ? collect([$app]) : collect([]);
        }

        return Inertia::render('Policies/Create', [
            'apps' => $apps,
        ]);
    }

    /**
     * Lưu policy mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'app_id' => 'required|exists:apps,id',
            'type' => 'required|in:membership_policy,privacy_policy',
            'content' => 'required|string',
        ]);

        AppPolicy::updateOrCreate(
            [
                'app_id' => $request->app_id,
                'type' => $request->type,
            ],
            [
                'content' => $request->content,
            ]
        );

        return redirect()->route('admin.policies.index')
            ->with('success', 'Chính sách đã được tạo thành công');
    }

    /**
     * Hiển thị danh sách policies
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

        $policies = AppPolicy::with(['app'])
            ->when($appId, function ($query, $appId) {
                return $query->where('app_id', $appId);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderByDesc('updated_at')
            ->paginate(20);

        $apps = $user->hasRole('admin') ? App::active()->get() : collect([$app]);

        return Inertia::render('Policies/Index', [
            'policies' => $policies,
            'apps' => $apps,
            'filters' => $request->only(['type', 'app_id']),
            'currentAppId' => $appId,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa policy
     */
    public function edit(AppPolicy $policy, Request $request)
    {
        $user = $request->user();
        $apps = $user->hasRole('admin') ? App::active()->get() : [];

        // Nếu là app_owner, chỉ có thể tạo policy cho app của mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $apps = $app ? collect([$app]) : collect([]);
        }

        return Inertia::render('Policies/Edit', [
            'policy' => $policy,
            'apps' => $apps,
        ]);
    }

    /**
     * Cập nhật policy
     */
    public function update(Request $request, AppPolicy $policy)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:membership_policy,privacy_policy',
        ]);

        $policy->update([
            'content' => $request->content,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.policies.index')
            ->with('success', 'Chính sách đã được cập nhật');
    }

    /**
     * Xóa policy
     */
    public function destroy(AppPolicy $policy)
    {
        $policy->delete();

        return redirect()->route('admin.policies.index')
            ->with('success', 'Chính sách đã được xóa');
    }
}
