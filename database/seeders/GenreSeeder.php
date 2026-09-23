<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genre;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Genre::firstOrCreate(['genre' => '小説']);
        Genre::firstOrCreate(['genre' => 'ビジネス']);
        Genre::firstOrCreate(['genre' => '技術書']);
        Genre::firstOrCreate(['genre' => '自己啓発']);
        Genre::firstOrCreate(['genre' => 'エッセイ']);
        Genre::firstOrCreate(['genre' => '歴史']);
        Genre::firstOrCreate(['genre' => '科学']);
        Genre::firstOrCreate(['genre' => '芸術']);
        Genre::firstOrCreate(['genre' => '料理']);
        Genre::firstOrCreate(['genre' => '旅行']);
    }
}
