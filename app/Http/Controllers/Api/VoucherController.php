<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherUserWallet;
use App\Models\Transaction;
use App\Models\PointsLedger;
use App\Models\AppUserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    /**
     * Lấy danh sách vouchers
     */
    public function index(Request $request)
    {
        $appId = $request->app_id;
        $categoryId = $request->query('category_id');
        $keyword = $request->query('keyword');
        $pointsLt = $request->query('points_lt');
        $pointsGte = $request->query('points_gte');
        $sort = $request->query('sort', 'latest');

        $vouchers = Voucher::forApp($appId)
                          ->active()
                          ->notExpired()
                          ->available()
                          ->with(['category', 'app'])
                          ->when($categoryId, function ($query, $categoryId) {
                              return $query->where('category_id', $categoryId);
                          })
                          ->when($keyword, function ($query, $keyword) {
                              return $query->where('name', 'like', "%{$keyword}%");
                          })
                          ->when($pointsLt, function ($query, $pointsLt) {
                              return $query->where('required_points', '<', $pointsLt);
                          })
                          ->when($pointsGte, function ($query, $pointsGte) {
                              return $query->where('required_points', '>=', $pointsGte);
                          });

        switch ($sort) {
            case 'points_asc':
                $vouchers->orderBy('required_points');
                break;
            case 'points_desc':
                $vouchers->orderByDesc('required_points');
                break;
            case 'expire_soon':
                $vouchers->orderBy('expire_at');
                break;
            default:
                $vouchers->orderByDesc('created_at');
        }

        $vouchers = $vouchers->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    /**
     * Lấy vouchers mới nhất
     */
    public function latest(Request $request)
    {
        $appId = $request->app_id;

        $vouchers = Voucher::forApp($appId)
                          ->active()
                          ->notExpired()
                          ->available()
                          ->with(['category', 'app'])
                          ->orderByDesc('created_at')
                          ->limit(20)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    /**
     * Đổi voucher
     */
    public function redeem(Request $request, $id)
    {
        $user = $request->user();
        $appId = $request->app_id;

        $voucher = Voucher::forApp($appId)->findOrFail($id);

        if (!$voucher->canRedeem()) {
            return response()->json([
                'success' => false,
                'error' => 'Voucher không thể đổi'
            ], 400);
        }

        $profile = AppUserProfile::where('user_id', $user->id)
                                ->where('app_id', $appId)
                                ->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'error' => 'Profile not found'
            ], 404);
        }

        if ($profile->points_total < $voucher->required_points) {
            return response()->json([
                'success' => false,
                'error' => 'Không đủ điểm để đổi voucher'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Trừ điểm
            $profile->decrement('points_total', $voucher->required_points);

            // Tạo entry trong wallet
            $walletEntry = VoucherUserWallet::create([
                'user_id' => $user->id,
                'app_id' => $appId,
                'voucher_id' => $voucher->id,
                'code' => strtoupper(Str::random(8)),
                'status' => 'redeemed',
                'redeemed_at' => now(),
                'expire_at' => $voucher->expire_at,
            ]);

            // Ghi points ledger
            PointsLedger::create([
                'user_id' => $user->id,
                'app_id' => $appId,
                'phone_snapshot' => $user->phone,
                'amount' => -$voucher->required_points,
                'reason' => "Đổi voucher: {$voucher->name}",
                'ref_type' => 'voucher_redeem',
                'ref_id' => $voucher->id,
            ]);

            // Ghi transaction
            Transaction::create([
                'user_id' => $user->id,
                'app_id' => $appId,
                'voucher_id' => $voucher->id,
                'type' => 'redeem',
                'status' => 'success',
                'metadata' => [
                    'code' => $walletEntry->code,
                    'points_used' => $voucher->required_points,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $walletEntry->load(['voucher', 'app'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi đổi voucher'
            ], 500);
        }
    }

    /**
     * Sử dụng voucher
     */
    public function use(Request $request, $code)
    {
        $user = $request->user();
        $appId = $request->app_id;

        $walletEntry = VoucherUserWallet::where('code', $code)
                                       ->where('user_id', $user->id)
                                       ->where('app_id', $appId)
                                       ->with(['voucher'])
                                       ->first();

        if (!$walletEntry) {
            return response()->json([
                'success' => false,
                'error' => 'Voucher không tìm thấy'
            ], 404);
        }

        if (!$walletEntry->canUse()) {
            return response()->json([
                'success' => false,
                'error' => 'Voucher không thể sử dụng'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Cập nhật status
            $walletEntry->update([
                'status' => 'used',
                'used_at' => now(),
            ]);

            // Ghi transaction
            Transaction::create([
                'user_id' => $user->id,
                'app_id' => $appId,
                'voucher_id' => $walletEntry->voucher_id,
                'type' => 'use',
                'status' => 'success',
                'metadata' => [
                    'code' => $code,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $walletEntry->fresh()->load(['voucher', 'app'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi sử dụng voucher'
            ], 500);
        }
    }

    /**
     * Xem ví voucher
     */
    public function wallet(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;

        $walletEntries = VoucherUserWallet::where('user_id', $user->id)
                                         ->where('app_id', $appId)
                                         ->with(['voucher', 'app'])
                                         ->orderByDesc('created_at')
                                         ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $walletEntries
        ]);
    }

    /**
     * Lịch sử giao dịch
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $appId = $request->app_id;

        $transactions = Transaction::where('user_id', $user->id)
                                  ->where('app_id', $appId)
                                  ->with(['voucher', 'app'])
                                  ->orderByDesc('created_at')
                                  ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
