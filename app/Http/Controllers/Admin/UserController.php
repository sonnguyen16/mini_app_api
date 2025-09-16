<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AppUserProfile;
use App\Models\User;
use App\Models\App;
use App\Models\PointsLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách users
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;
        $app = null;

        // Nếu là app_owner, chỉ xem users của app mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $appId = $app ? $app->id : null;
        }

        $profiles = AppUserProfile::with(['user', 'app'])
            ->when($appId, function ($query, $appId) {
                return $query->where('app_id', $appId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhereHas('user', function ($q) use ($search) {
                               $q->where('phone', 'like', "%{$search}%");
                           });
            })
            ->when($request->active !== null, function ($query) use ($request) {
                return $query->where('active', $request->active);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        $apps = $user->hasRole('admin') ? App::active()->get() : collect([$app]);

        return Inertia::render('Users/Index', [
            'profiles' => $profiles,
            'apps' => $apps,
            'filters' => $request->only(['search', 'active', 'app_id']),
            'currentAppId' => $appId,
        ]);
    }

    /**
     * Hiển thị form tạo user mới
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

        return Inertia::render('Users/Create', [
            'apps' => $apps,
        ]);
    }

    /**
     * Lưu user mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'name' => 'required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'app_id' => 'required|exists:apps,id',
        ]);

        DB::beginTransaction();
        try {
            // Tìm hoặc tạo user
            $user = null;
            if ($request->phone) {
                $user = User::where('phone', $request->phone)->first();
            }

            if (!$user) {
                $user = User::create([
                    'phone' => $request->phone,
                    'password' => bcrypt('user123'), // Default password
                ]);
                $user->assignRole('end_user');
            }

            // Tạo profile trong app
            AppUserProfile::create([
                'user_id' => $user->id,
                'app_id' => $request->app_id,
                'name' => $request->name,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'address' => $request->address,
                'active' => true,
            ]);

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User đã được tạo thành công');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi tạo user']);
        }
    }

    /**
     * Hiển thị chi tiết user
     */
    public function show(AppUserProfile $profile)
    {
        $profile->load(['user', 'app', 'voucherWallet.voucher', 'pointsLedger']);

        // Tính toán thống kê điểm
        $pointsStats = [
            'total_earned' => $profile->pointsLedger()->where('amount', '>', 0)->sum('amount'),
            'total_spent' => abs($profile->pointsLedger()->where('amount', '<', 0)->sum('amount')),
        ];

        // Thống kê voucher
        $voucherStats = [
            'redeemed' => $profile->voucherWallet()->count(),
            'used' => $profile->voucherWallet()->where('status', 'used')->count(),
        ];

        return Inertia::render('Users/Show', [
            'profile' => $profile,
            'pointsHistory' => $profile->pointsLedger()->orderBy('created_at', 'desc')->get(),
            'voucherWallet' => $profile->voucherWallet()->with('voucher')->get(),
            'pointsStats' => $pointsStats,
            'voucherStats' => $voucherStats,
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit(AppUserProfile $profile)
    {
        $user = request()->user();
        $apps = $user->hasRole('admin') ? App::active()->get() : [];

        // Nếu là app_owner, chỉ có thể tạo policy cho app của mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $apps = $app ? collect([$app]) : collect([]);
        }
        return Inertia::render('Users/Edit', [
            'profile' => $profile->load(['user', 'app']),
            'apps' => $apps,
        ]);
    }

    /**
     * Cập nhật user
     */
    public function update(Request $request, AppUserProfile $profile)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'nullable|string|min:6',
            'app_id' => 'required|exists:apps,id',
            'name' => 'required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật thông tin user (phone, password)
            $user = $profile->user;
            $userData = ['phone' => $request->phone];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Cập nhật profile
            $profile->update($request->only([
                'app_id', 'name', 'birthday', 'gender', 'address', 'active'
            ]));

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', 'User đã được cập nhật');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật user']);
        }
    }

    /**
     * Cộng/trừ điểm cho user
     */
    public function addPoints(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'app_id' => 'required|exists:apps,id',
            'amount' => 'required|integer|not_in:0',
            'reason' => 'nullable|string|max:255',
        ]);

        // Tìm user profile
        $profile = AppUserProfile::whereHas('user', function ($query) use ($request) {
            $query->where('phone', $request->phone);
        })->where('app_id', $request->app_id)->first();

        if (!$profile) {
            return back()->withErrors(['phone' => 'Không tìm thấy user với số điện thoại này trong app']);
        }

        // Kiểm tra điểm âm
        if ($request->amount < 0 && $profile->points_total + $request->amount < 0) {
            return back()->withErrors(['amount' => 'Không đủ điểm để trừ']);
        }

        DB::beginTransaction();
        try {
            // Cập nhật điểm
            $profile->increment('points_total', $request->amount);

            // Ghi ledger
            PointsLedger::create([
                'user_id' => $profile->user_id,
                'app_id' => $request->app_id,
                'phone_snapshot' => $profile->user->phone,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'ref_type' => 'manual_adjustment',
                'created_by' => $request->user()->id,
            ]);

            DB::commit();

            return back()->with('success', 'Điểm đã được cập nhật thành công');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật điểm']);
        }
    }

    /**
     * Tìm user bằng QR code
     */
    public function findByQR(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'app_id' => 'required|exists:apps,id',
        ]);

        // Giả sử QR code chứa phone number
        $phone = $request->phone;

        $profile = AppUserProfile::whereHas('user', function ($query) use ($phone) {
            $query->where('phone', $phone);
        })->where('app_id', $request->app_id)->with(['user', 'app'])->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy user'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }
}
