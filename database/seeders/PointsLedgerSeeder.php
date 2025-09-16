<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\AppUserProfile;
use App\Models\PointsLedger;
use App\Models\User;

class PointsLedgerSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $apps = App::all();
        $adminUser = User::role('admin')->first();

        foreach ($apps as $app) {
            $profiles = AppUserProfile::where('app_id', $app->id)->get();
            
            // Tạo 30 entries cho mỗi app
            for ($i = 1; $i <= 30; $i++) {
                $profile = $profiles->random();
                $amount = fake()->randomElement([10, 20, 50, 100, -50, -100]);
                
                PointsLedger::create([
                    'user_id' => $profile->user_id,
                    'app_id' => $app->id,
                    'phone_snapshot' => $profile->user->phone,
                    'amount' => $amount,
                    'reason' => $amount > 0 ? 'Tích điểm mua hàng' : 'Đổi voucher',
                    'ref_type' => $amount > 0 ? 'purchase' : 'voucher_redeem',
                    'ref_id' => fake()->numberBetween(1, 100),
                    'created_by' => $adminUser->id,
                ]);
            }
        }
    }
}
