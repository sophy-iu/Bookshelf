<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'yamada@example.com')->firstOrFail();

        $book1 = Book::firstOrCreate(
            ['isbn' => '9784101010014'],
            [
                'user_id' => $user->id,
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
                'date' => '1905-01-01',
                'description' => '名前のない一匹の猫の視点から、当時の人間たちの滑稽な行動や社会風俗をユーモアと皮肉たっぷりに描いた長編小説',
            ]
        );

        $genre = Genre::where('name', '小説')->first();

        $book1->genres()->sync([$genre->id]);

        $book2 = Book::firstOrCreate(
            ['isbn' => '9784422100524'],
            [
                'user_id' => $user->id,
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
                'date' => '1936-10-01',
                'description' => 'ビジネス現場においてだけでなく、家族・恋愛・交遊関係など老若男女問わず、あらゆるシチュエーションにおいて本書に挙げられているメソッドは有効であり、人生を豊かに過ごすための「人との付き合い方」を学ぶことができる',
            ]
        );

        $genre = Genre::whereIn('name', ['ビジネス','自己啓発'])->get();

        $book2->genres()->sync($genre->pluck('id'));

        $book3 = Book::firstOrCreate(
            ['isbn' => '9784873115658'],
            [
                'user_id' => $user->id,
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
                'date' => '2012-06-23',
                'description' => 'コードは理解しやすくなければならない。本書はこの原則を日々のコーディングの様々な場面に当てはめる方法を紹介する。名前の付け方、コメントの書き方など表面上の改善について。コードを動かすための制御フロー、論理式、変数などループとロジックについて。またコードを再構成するための方法。さらにテストの書き方などについて、楽しいイラストと共に説明する。',
            ]
        );

        $genre = Genre::where('name', '技術書')->first();

        $book3->genres()->sync([$genre->id]);

        $book4 = Book::firstOrCreate(
            ['isbn' => '9784863940246'],
            [
                'user_id' => $user->id,
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
                'date' => '2013-08-30',
                'description' => '長期的・継続的に成果を出すための原則をまとめた世界的ベストセラー書籍',
            ]
        );

        $genre = Genre::whereIn('name', ['ビジネス','自己啓発'])->get();

        $book4->genres()->sync($genre->pluck('id'));

        $book5 = Book::firstOrCreate(
            ['isbn' => '9784101010021'],
            [
                'user_id' => $user->id,
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
                'date' => '1906-04-01',
                'description' => '直情径行で正義感の強い江戸っ子の青年が、赴任した四国・松山の田舎中学で悪賢い偽善者たちと戦う痛快なユーモア小説',
            ]
        );

        $genre = Genre::where('name', '小説')->first();

        $book5->genres()->sync([$genre->id]);

        $book6 = Book::firstOrCreate(
            ['isbn' => '9784309226712'],
            [
                'user_id' => $user->id,
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
                'date' => '2016-09-08',
                'description' => 'ホモ・サピエンスの誕生から現代までの7万年の歴史を壮大なスケールで描いた世界的な大ベストセラー',
            ]
        );

        $genre = Genre::whereIn('name', ['歴史','科学'])->get();

        $book6->genres()->sync($genre->pluck('id'));

        $book7 = Book::firstOrCreate(
            ['isbn' => '9784048930598'],
            [
                'user_id' => $user->id,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
                'date' => '2017-12-18',
                'description' => 'プログラミングにおける可読性・保守性を高めるための最高峰のバイブルとされる名著',
            ]
        );

        $genre = Genre::where('name', '技術書')->first();

        $book7->genres()->sync([$genre->id]);

        $book8 = Book::firstOrCreate(
            ['isbn' => '9784478025819'],
            [
                'user_id' => $user->id,
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
                'date' => '2013-12-13',
                'description' => '心理学界の巨匠アルフレッド・アドラーの思想（アドラー心理学）を、悩める「青年」と「哲人」の熱い対話形式で分かりやすく解き明かした一冊',
            ]
        );

        $genre = Genre::where('name', '自己啓発')->first();

        $book8->genres()->sync([$genre->id]);

        $book9 = Book::firstOrCreate(
            ['isbn' => '9784163902302'],
            [
                'user_id' => $user->id,
                'title' => ' 火花',
                'author' => ' 又吉直樹',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
                'date' => '2015-03-11',
                'description' => '売れない若手芸人の徳永と、彼が師と仰ぐ天才肌の先輩芸人・神谷の2人を中心に、「お笑いとは何か」「生きるとは何か」を純文学の筆致でリアルかつ鮮烈に描き出しています。',
            ]
        );

        $genre = Genre::where('name', '小説')->first();

        $book9->genres()->sync([$genre->id]);

        $book10 = Book::firstOrCreate(
            ['isbn' => '9784822289607'],
            [
                'user_id' => $user->id,
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
                'date' => '2019-01-11',
                'description' => 'データや事実（ファクト）に基づいて、世界を正しく見る（フルネス）重要性」を説いている点にあります。私たちは、ニュースや思い込みによって「世界はどんどん悪くなっている」と誤解しがちですが、客観的なデータを見れば「世界は確実に着実に良くなっている」ことが分かります。',
            ]
        );

        $genre = Genre::whereIn('name', ['ビジネス','科学'])->get();

        $book10->genres()->sync($genre->pluck('id'));

        $book11 = Book::firstOrCreate(
            ['isbn' => '9784822251468'],
            [
                'user_id' => $user->id,
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
                'date' => '2007-01-18',
                'description' => '私たちが普段気にも留めないただの「鉄の箱（コンテナ）」が、いかにして世界経済の仕組みを根本から変え、グローバライゼーションを爆発的に加速させたかをダイナミックに描き出しています。',
            ]
        );

        $genre = Genre::whereIn('name', ['ビジネス','歴史'])->get();

        $book11->genres()->sync($genre->pluck('id'));
    }
}
