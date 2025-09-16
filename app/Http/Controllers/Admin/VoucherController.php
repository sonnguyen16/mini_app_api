<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Voucher;
use App\Models\Category;
use App\Models\App;
use Illuminate\Support\Facades\Storage;

class VoucherController extends Controller
{
    /**
     * Hiển thị danh sách vouchers
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;
        $app = null;

        // Nếu là app_owner, chỉ xem vouchers của app mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $appId = $app ? $app->id : null;
        }

        $vouchers = Voucher::with(['app', 'category'])
            ->when($appId, function ($query, $appId) {
                return $query->where('app_id', $appId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->active !== null, function ($query) use ($request) {
                return $query->where('active', $request->active);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $apps = $user->hasRole('admin') ? App::active()->get() : collect([$app]);
        $categories = Category::with('app')->active()->get();

        return Inertia::render('Vouchers/Index', [
            'vouchers' => $vouchers,
            'apps' => $apps,
            'categories' => $categories,
            'filters' => $request->only(['search', 'active', 'app_id', 'category_id']),
            'currentAppId' => $appId,
        ]);
    }

    /**
     * Hiển thị form tạo voucher mới
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

        $categories = Category::with('app')->active()->get();

        return Inertia::render('Vouchers/Create', [
            'apps' => $apps,
            'categories' => $categories,
        ]);
    }

    /**
     * Lưu voucher mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detail' => 'nullable|string',
            'usage_condition' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'required_points' => 'required|integer|min:1',
            'expire_at' => 'nullable|date|after:today',
            'quantity' => 'nullable|integer|min:1',
            'app_id' => 'required|exists:apps,id',
            'category_id' => 'required|exists:categories,id',
            'active' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('vouchers', 'public');
        }

        Voucher::create([
            'name' => $request->name,
            'description' => $request->description,
            'detail' => $request->detail,
            'usage_condition' => $request->usage_condition,
            'image' => $imagePath,
            'required_points' => $request->required_points,
            'expire_at' => $request->expire_at,
            'quantity' => $request->quantity,
            'app_id' => $request->app_id,
            'category_id' => $request->category_id,
            'active' => $request->active ?? true,
        ]);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher đã được tạo thành công');
    }

    /**
     * Hiển thị chi tiết voucher
     */
    public function show(Voucher $voucher)
    {
        $voucher->load(['app', 'category', 'walletEntries.user', 'transactions']);

        // Tính toán thống kê
        $stats = [
            'redeemed' => $voucher->walletEntries()->count(),
            'used' => $voucher->walletEntries()->where('status', 'used')->count(),
            'available' => $voucher->walletEntries()->where('status', 'redeemed')->count(),
            'remaining' => $voucher->quantity ? max(0, $voucher->quantity - $voucher->walletEntries()->count()) : null,
        ];

        // Lấy danh sách đổi voucher gần đây
        $recentRedemptions = $voucher->walletEntries()
            ->with('user')
            ->orderBy('redeemed_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Vouchers/Show', [
            'voucher' => $voucher,
            'stats' => $stats,
            'recentRedemptions' => $recentRedemptions,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit(Voucher $voucher)
    {
        $user = request()->user();
        $apps = $user->hasRole('admin') ? App::active()->get() : [];

        // Nếu là app_owner, chỉ có thể tạo policy cho app của mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $apps = $app ? collect([$app]) : collect([]);
        }

        $categories = Category::with('app')->active()->get();

        return Inertia::render('Vouchers/Edit', [
            'voucher' => $voucher->load(['app', 'category']),
            'apps' => $apps,
            'categories' => $categories,
        ]);
    }

    /**
     * Cập nhật voucher
     */
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'detail' => 'nullable|string',
            'usage_condition' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'required_points' => 'required|integer|min:1',
            'expire_at' => 'nullable|date',
            'quantity' => 'nullable|integer|min:1',
            'app_id' => 'required|exists:apps,id',
            'category_id' => 'required|exists:categories,id',
            'active' => 'boolean',
        ]);

        $data = $request->only([
            'name', 'description', 'detail', 'usage_condition', 'required_points',
            'expire_at', 'quantity', 'app_id', 'category_id', 'active'
        ]);

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ
            if ($voucher->image) {
                Storage::disk('public')->delete($voucher->image);
            }
            $data['image'] = $request->file('image')->store('vouchers', 'public');
        }

        $voucher->update($data);

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher đã được cập nhật');
    }

    /**
     * Xóa voucher
     */
    public function destroy(Voucher $voucher)
    {
        // Xóa ảnh
        if ($voucher->image) {
            Storage::disk('public')->delete($voucher->image);
        }

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')
            ->with('success', 'Voucher đã được xóa');
    }

    /**
     * Lấy categories theo app_id (AJAX)
     */
    public function getCategoriesByApp(Request $request, $appId)
    {
        $categories = Category::where('app_id', $appId)->active()->get();

        return response()->json($categories);
    }

    public function toggleStatus(Voucher $voucher)
    {
        $voucher->active = !$voucher->active;
        $voucher->save();

        return redirect()->back()->with('success', 'Trạng thái voucher đã được thay đổi');
    }
}
