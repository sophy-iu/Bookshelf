<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_書籍一覧を取得できる(): void
    {
        
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }
}
