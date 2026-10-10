<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_ジャンルは複数の書籍を持つ(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $book1 = Book::factory()->create([
            'title' => '書籍1',
        ]);

        $book2 = Book::factory()->create([
            'title' => '書籍2',
        ]);

        $genre->books()->attach([
            $book1->id,
            $book2->id,
        ]);

        $this->assertCount(2, $genre->books);

        $this->assertTrue(
            $genre->books->contains($book1)
        );

        $this->assertTrue(
            $genre->books->contains($book2)
        );
    }
}