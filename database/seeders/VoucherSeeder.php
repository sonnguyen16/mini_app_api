<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\Category;
use App\Models\Voucher;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $apps = App::all();

        foreach ($apps as $app) {
            $categories = Category::where('app_id', $app->id)->get();

            // Tạo 20 vouchers cho mỗi app
            for ($i = 1; $i <= 20; $i++) {
                $randomNumber = rand(1, 5);
                Voucher::create([
                    'app_id' => $app->id,
                    'category_id' => $categories->random()->id,
                    'name' => "Voucher {$i} - {$app->name}",
                    'description' => fake()->sentence(),
                    'image' => "vouchers/voucher{$randomNumber}.jpg",
                    'detail' => fake()->paragraphs(3, true),
                    'required_points' => fake()->randomElement([50, 100, 200, 300, 500]),
                    'expire_at' => fake()->dateTimeBetween('now', '+6 months'),
                    'usage_condition' => fake()->paragraphs(2, true),
                    'quantity' => fake()->optional(0.7)->numberBetween(10, 100),
                    'active' => fake()->boolean(90),
                ]);
            }
        }
    }
}
