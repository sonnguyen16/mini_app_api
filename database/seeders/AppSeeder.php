<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AppSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Tạo admin user
        $admin = User::create([
            'email' => 'admin@miniapp.com',
            'password' => Hash::make('admin123'),
            'phone' => '0901234567',
        ]);
        $admin->assignRole('admin');

        // Tạo app 1
        $app1 = App::create([
            'name' => 'Coffee Shop Rewards',
            'description' => 'Hệ thống tích điểm cho chuỗi cà phê',
            'owner_email' => 'coffee@owner.com',
            'owner_password_hash' => Hash::make('coffee123'),
            'owner_name' => 'Coffee Shop',
            'mini_app_id' => 'coffee_app_001',
            'active' => true,
        ]);

        // Tạo app owner cho app 1
        $coffeeOwner = User::create([
            'email' => 'coffee@owner.com',
            'password' => Hash::make('coffee123'),
            'phone' => '0901234568',
        ]);
        $coffeeOwner->assignRole('app_owner');

        // Tạo app 2
        $app2 = App::create([
            'name' => 'Beauty Salon Points',
            'description' => 'Hệ thống tích điểm cho salon làm đẹp',
            'owner_email' => 'beauty@owner.com',
            'owner_password_hash' => Hash::make('beauty123'),
            'owner_name' => 'Beauty Salon',
            'mini_app_id' => 'beauty_app_002',
            'active' => true,
        ]);

        // Tạo app owner cho app 2
        $beautyOwner = User::create([
            'email' => 'beauty@owner.com',
            'password' => Hash::make('beauty123'),
            'phone' => '0901234569',
        ]);
        $beautyOwner->assignRole('app_owner');
    }
}
