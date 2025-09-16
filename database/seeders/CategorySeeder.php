<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $apps = App::all();

        $categoryData = [
            ['name' => 'Đồ uống', 'icon' => 'fas fa-coffee'],
            ['name' => 'Thức ăn', 'icon' => 'fas fa-hamburger'],
            ['name' => 'Giảm giá', 'icon' => 'fas fa-percentage'],
            ['name' => 'Quà tặng', 'icon' => 'fas fa-gift'],
            ['name' => 'Dịch vụ', 'icon' => 'fas fa-concierge-bell'],
        ];

        foreach ($apps as $app) {
            foreach ($categoryData as $category) {
                Category::create([
                    'app_id' => $app->id,
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'active' => true,
                ]);
            }
        }
    }
}
