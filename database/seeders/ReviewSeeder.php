<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Book;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 山田
        $book = Book::where('isbn', '9784101010014')->first();

        $user = User::where('email', 'yamada@example.com')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '物語の展開が面白く、最後まで楽しんで読めました。',
        ]);

        $book = Book::where('isbn', '9784422100524')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '具体例が多く、日常でも実践しやすい内容でした。',
        ]);
        
        $book = Book::where('isbn', '9784873115658')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'コードを書く上で大切な考え方を学べました。',
        ]);
        
        $book = Book::where('isbn', '9784863940246')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '興味深い内容でしたが、少し難しく感じる部分もありました。',
        ]);

        $book = Book::where('isbn', '9784101010021')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '読みやすく、考えさせられる内容でした。',
        ]);

        $book = Book::where('isbn', '9784309226712')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '歴史の流れが分かりやすくまとめられていました。',
        ]);

        $book = Book::where('isbn', '9784048930598')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '新しい知識が多く、とても勉強になりました。',
        ]);

        // 鈴木
        $book = Book::where('isbn', '9784101010014')->first();

        $user = User::where('email', 'suzuki@example.com')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '登場人物が魅力的で、楽しく読むことができました。',
        ]);

        $book = Book::where('isbn', '9784422100524')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '人との接し方について改めて考えるきっかけになりました。',
        ]);

        $book = Book::where('isbn', '9784873115658')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '実際の開発でも活用できそうな内容が多かったです。',
        ]);

        $book = Book::where('isbn', '9784478025819')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '参考になる部分はありましたが、もう少し詳しい説明が欲しかったです。',
        ]);

        $book = Book::where('isbn', '9784163902302')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '作品の背景まで知ることができ、とても楽しめました。',
        ]);

        $book = Book::where('isbn', '9784822289607')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '旅行前に読んでおくと役立つ情報が多いと思います。',
        ]);

        $book = Book::where('isbn', '9784822251468')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'テンポよく読めて、全体的に楽しめました。',
        ]);

        // 田中
        $book = Book::where('isbn', '9784101010014')->first();

        $user = User::where('email', 'tanaka@example.com')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '独特な文章表現が印象に残りました。',
        ]);

        $book = Book::where('isbn', '9784863940246')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '自分の考え方を見直す良いきっかけになりました。',
        ]);

        $book = Book::where('isbn', '9784101010021')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '文章が読みやすく、内容にも共感できました。',
        ]);

        $book = Book::where('isbn', '9784309226712')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '情報量が多く、じっくり読むのに向いている本だと思います。',
        ]);

        $book = Book::where('isbn', '9784048930598')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '難しいテーマが分かりやすく説明されていました。',
        ]);

        $book = Book::where('isbn', '9784478025819')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '知らなかった作品についても興味を持つことができました。',
        ]);

        // 佐藤
        $book = Book::where('isbn', '9784422100524')->first();

        $user = User::where('email', 'sato@example.com')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '仕事だけでなく普段の人間関係にも役立つと思いました。',
        ]);

        $book = Book::where('isbn', '9784873115658')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '読みやすいコードについて具体的に理解できました。',
        ]);

        $book = Book::where('isbn', '9784863940246')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '前向きになれる内容で、何度か読み返したいと思います。',
        ]);

        $book = Book::where('isbn', '9784478025819')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '読みやすく、考えさせられる内容でした。',
        ]);

        $book = Book::where('isbn', '9784822289607')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'データが分かりやすくまとめられていました。',
        ]);

        $book = Book::where('isbn', '9784822251468')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '読みやすかったですが、好みが分かれる内容だと思います。',
        ]);

        // 高橋
        $book = Book::where('isbn', '9784101010021')->first();

        $user = User::where('email', 'takahashi@example.com')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '気軽に読める一方で、印象に残る部分も多かったです。',
        ]);

        $book = Book::where('isbn', '9784309226712')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '歴史についてさらに調べてみたくなる内容でした。',
        ]);

        $book = Book::where('isbn', '9784048930598')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '少し専門的でしたが、興味深い内容でした。',
        ]);

        $book = Book::where('isbn', '9784478025819')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '分かりやすい説明で、実際に試してみたくなりました。',
        ]);

        $book = Book::where('isbn', '9784163902302')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '芸人という職業の葛藤や孤独を描いた点が、印象的でした。',
        ]);

        $book = Book::where('isbn', '9784822289607')->first();

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '身近な例があり、科学を楽しく学ぶことができました。',
        ]);
    }
}