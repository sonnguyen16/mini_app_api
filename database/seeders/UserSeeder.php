<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\App;
use App\Models\AppUserProfile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $apps = App::all();
        
        // Tạo 20 end users
        for ($i = 1; $i <= 20; $i++) {
            $user = User::create([
                'email' => "user{$i}@example.com",
                'password' => Hash::make('user123'),
                'phone' => '090123' . str_pad($i + 4569, 4, '0', STR_PAD_LEFT),
            ]);
            $user->assignRole('end_user');

            // Tạo profile cho user trong mỗi app
            foreach ($apps as $app) {
                AppUserProfile::create([
                    'user_id' => $user->id,
                    'app_id' => $app->id,
                    'name' => "User {$i}",
                    'birthday' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'address' => fake()->address(),
                    'points_total' => fake()->numberBetween(0, 1000),
                    'active' => true,
                ]);
            }
        }
    }
}
