<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/',[BookController::class,'index']) ->name('books.index');

Route::middleware(['auth'])->group(function() {
       Route::get('/books/create', [BookController::class, 'create']) ->name('books.create');
       Route::post('/books', [BookController::class, 'store'])->name('books.store');
       Route::get('/books/{book}/edit', [BookController::class, 'edit']) ->name('books.edit');
       Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
       Route::put('/books/{book}/update', [BookController::class, 'update']) ->name('books.update');

       Route::post('/books/{book}/reviews', [BookController::class, 'store'])->name('reviews.store');
       Route::post('/reviews/{review}/like', [ReviewController::class, 'toggleLike'])->name('reviews.like');
       Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
       Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
       Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

       Route::get('/genres', [GenreController::class, 'index']) ->name('genres.index');
       Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
       Route::get('/genres/{genre}', [GenreController::class, 'show']) ->name('genres.show');
       Route::get('/genres/{genre}/edit', [GenreController::class, 'edit']) ->name('genres.edit');
       Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

       Route::get('/favorites', [FavoriteController::class, 'index']) ->name('favorites.index');
       Route::post('/favorites/{book}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

       Route::get('/ranking', [BookController::class, 'ranking']) ->name('ranking.index');
});

Route::get('/books/{book}', [BookController::class, 'show']) ->name('books.show');