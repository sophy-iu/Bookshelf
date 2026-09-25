<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 山田
        $user = User::where('email', 'yamada@example.com')->first();

        $bookIds = Book::whereIn('isbn',['9784101010014','9784873115658','9784101010021','9784048930598'])->pluck('id');

        $user->favorites()->syncWithoutDetaching($bookIds);

        // 鈴木
        $user = User::where('email', 'suzuki@example.com')->first();

        $bookIds = Book::whereIn('isbn',['9784422100524','9784478025819','9784822251468'])->pluck('id');

        $user->favorites()->syncWithoutDetaching($bookIds);

        // 田中
        $user = User::where('email', 'suzuki@example.com')->first();

        $bookIds = Book::whereIn('isbn',['9784863940246','9784101010021','9784478025819'])->pluck('id');

        $user->favorites()->syncWithoutDetaching($bookIds);

        // 佐藤
        $user = User::where('email', 'sato@example.com')->first();

        $bookIds = Book::whereIn('isbn',['9784873115658','9784863940246','9784822289607'])->pluck('id');

        $user->favorites()->syncWithoutDetaching($bookIds);

        // 高橋
        $user = User::where('email', 'takahashi@example.com')->first();

        $bookIds = Book::whereIn('isbn',['9784101010021','9784309226712','9784048930598','9784163902302'])->pluck('id');

        $user->favorites()->syncWithoutDetaching($bookIds);
    }
}
