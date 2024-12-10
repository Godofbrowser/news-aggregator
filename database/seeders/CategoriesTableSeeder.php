<?php

namespace Database\Seeders;

use App\Models\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Category::query()->exists()) {
            Category::query()->create([
                'name' => 'Generic'
            ]);
        }
    }
}
