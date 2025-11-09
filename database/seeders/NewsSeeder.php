<?php

namespace Database\Seeders;

use App\Models\Media\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        News::factory(10)->withTags(3)->create();
    }
}
