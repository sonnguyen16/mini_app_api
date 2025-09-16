<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\App;
use App\Models\AppUserProfile;
use App\Models\Voucher;
use App\Models\Transaction;

class DashboardController extends Controller
{
    /**
     * Hiển thị dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $appId = null;

        // Nếu là app_owner, chỉ xem data của app mình
        if ($user->hasRole('app_owner')) {
            $app = App::where('owner_email', $user->email)->first();
            $appId = $app ? $app->id : null;
        }

        // Thống kê tổng quan
        $stats = [
            'total_users' => $appId
                ? AppUserProfile::where('app_id', $appId)->count()
                : AppUserProfile::count(),
            'total_vouchers' => $appId
                ? Voucher::where('app_id', $appId)->count()
                : Voucher::count(),
            'total_transactions' => $appId
                ? Transaction::where('app_id', $appId)->count()
                : Transaction::count(),
            'total_apps' => $user->hasRole('admin') ? App::count() : 1,
        ];

        // Giao dịch gần đây
        $recentTransactions = Transaction::with(['user', 'voucher', 'app'])
            ->when($appId, function ($query, $appId) {
                return $query->where('app_id', $appId);
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'userRole' => $user->getRoleNames()->first(),
            'currentApp' => $appId ? App::find($appId) : null,
        ]);
    }
}
