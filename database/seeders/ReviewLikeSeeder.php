<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 山田
        $user = User::where('email', 'yamada@example.com')->first();

        $reviewIds = Review::where('user_id','!=',$user->id)->orderBy('id')->ofset(0)->limit(5)->pluck('id');

        $user->reviewLikes()->syncWithoutDetaching($reviewIds);

        // 鈴木
        $user = User::where('email', 'suzuki@example.com')->first();

        $reviewIds = Review::where('user_id','!=',$user->id)->orderBy('id')->ofset(3)->limit(5)->pluck('id');

        $user->reviewLikes()->syncWithoutDetaching($reviewIds);

        // 田中
        $user = User::where('email', 'tanaka@example.com')->first();

        $reviewIds = Review::where('user_id','!=',$user->id)->orderBy('id')->ofset(6)->limit(5)->pluck('id');

        $user->reviewLikes()->syncWithoutDetaching($reviewIds);

        // 佐藤
        $user = User::where('email', 'sato@example.com')->first();

        $reviewIds = Review::where('user_id','!=',$user->id)->orderBy('id')->ofset(9)->limit(5)->pluck('id');

        $user->reviewLikes()->syncWithoutDetaching($reviewIds);

        // 高橋
        $user = User::where('email', 'takahashi@example.com')->first();

        $reviewIds = Review::where('user_id','!=',$user->id)->orderBy('id')->ofset(12)->limit(5)->pluck('id');

        $user->reviewLikes()->syncWithoutDetaching($reviewIds);
    }
}
