# BookShelf 書籍レビューアプリ

## 概要
BookShelfは、書籍の登録・検索やレビューの投稿を通して、読書を管理できる書籍レビューアプリです。
書籍へのレビュー・評価、お気に入り登録、ランキング表示に加え、読書計画の作成や通知機能を備えています。
また、Google Books APIを利用したISBN検索や、Laravel Sanctumによる認証付きの書籍APIも実装しています。

## 環境構築
1. リポジトリをクローン

```bash
git clone git@github.com:sophy-iu/Bookshelf.git
```

2. プロジェクトディレクトリへ移動

```bash
cd bookshelf-app
```

3. Composerパッケージをインストール

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer install
```

4. 「.env.example」ファイルをコピーして「.env」ファイルを作成

```bash
cp .env.example .env
```

5. `.env` ファイルを開き、データベース接続情報を以下のように設定

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

6. Google Books APIキーを `.env` に設定

```env
GOOGLE_BOOKS_API_KEY=取得したAPIキー
```

7. Dockerコンテナを起動

```bash
./vendor/bin/sail up -d
```

8. アプリケーションキーを作成

```bash
./vendor/bin/sail artisan key:generate
```

9. npmパッケージをインストール

```bash
./vendor/bin/sail npm install
```

10. マイグレーションとシーディングを実行

```bash
./vendor/bin/sail artisan migrate --seed
```

11. フロントエンド開発サーバーを起動

```bash
./vendor/bin/sail npm run dev
```

## 使用技術
- PHP 8.5
- Laravel 10.x
- MySQL 8.4
- Laravel Sail
- Laravel Fortify
- Laravel Sanctum
- Vite
- Tailwind CSS 3.4
- Alpine.js
- Google Books API
- phpMyAdmin
- nginx（Webサーバ）

## ER図
![ER図](index.drawio.png)

## URL
- 書籍一覧：GET http://localhost/
- ランキング：GET http://localhost/ranking
- 書籍詳細：GET http://localhost/books/{book}
- 書籍登録画面：GET http://localhost/books/create
- 書籍登録：POST http://localhost/books
- 書籍編集画面：GET http://localhost/books/{book}/edit
- 書籍更新：PUT http://localhost/books/{book}/update
- 書籍削除：DELETE http://localhost/books/{book}
- ISBN検索：GET http://localhost/books/isbn/{isbn}

- レビュー投稿：POST http://localhost/books/{book}/reviews
- レビューいいね：POST http://localhost/reviews/{review}/like
- レビュー編集画面：GET http://localhost/reviews/{review}/edit
- レビュー更新：PUT http://localhost/reviews/{review}
- レビュー削除：DELETE http://localhost/reviews/{review}

- ジャンル一覧：GET http://localhost/genres
- ジャンル登録画面：GET http://localhost/genres/create
- ジャンル登録：POST http://localhost/genres
- ジャンル詳細：GET http://localhost/genres/{genre}
- ジャンル編集画面：GET http://localhost/genres/{genre}/edit
- ジャンル更新：PUT http://localhost/genres/{genre}
- ジャンル削除：DELETE http://localhost/genres/{genre}

- お気に入り一覧：GET http://localhost/favorites
- お気に入り登録・解除：POST http://localhost/favorites/{book}

- レポート：GET http://localhost/reports

- 読書計画一覧：GET http://localhost/reading-plans
- 読書計画登録画面：GET http://localhost/reading-plans/create
- 読書計画登録：POST http://localhost/reading-plans
- 読書計画編集画面：GET http://localhost/reading-plans/{readingPlan}/edit
- 読書計画更新：PUT http://localhost/reading-plans/{readingPlan}
- 読書計画削除：DELETE http://localhost/reading-plans/{readingPlan}
- 読書計画完了：POST http://localhost/reading-plans/{readingPlan}/complete

- 通知一覧：GET http://localhost/notifications
- 通知既読：POST http://localhost/notifications/{notification}/read

### API

- APIログイン：POST http://localhost/api/v1/login
- 書籍一覧取得：GET http://localhost/api/v1/books
- 書籍詳細取得：GET http://localhost/api/v1/books/{book}
- 書籍登録：POST http://localhost/api/v1/books
- 書籍更新：PUT http://localhost/api/v1/books/{book}
- 書籍削除：DELETE http://localhost/api/v1/books/{book}

## 作成者
羽根田　哲太