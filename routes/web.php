<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
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
Route::get('/books/{book}', [BookController::class, 'show']) ->name('books.show');

Route::middleware(['auth'])->group(function() {
       Route::get('/books/create', [BookController::class, 'create']) ->name('books.create');
       Route::post('/books', [BookController::class, 'store'])->name('books.store');
       Route::get('/books/{book}/edit', [BookController::class, 'edit']);

       Route::get('/genres', [BookController::class, 'index']) ->name('genres.index');
       Route::get('/genres/{genre}', [BookController::class, 'show']) ->name('genres.show');
       Route::get('/genres/{genre}/edit', [BookController::class, 'edit']) ->name('genres.edit');

       Route::get('/favorites', [BookController::class, 'index']) ->name('favorites.index');

       Route::get('/ranking', [BookController::class, 'ranking']) ->name('ranking.index');
});