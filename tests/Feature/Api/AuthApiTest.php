<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しい認証情報でログインするとトークンを取得できる(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'token',
        ]);

        $this->assertNotEmpty(
            $response->json('token')
        );
    }

    public function test_パスワードが間違っている場合はログインできない(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    public function test_存在しないメールアドレスではログインできない(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }

    public function test_メールアドレスが未入力の場合は422になる(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_パスワードが未入力の場合は422になる(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'password',
        ]);
    }
}
