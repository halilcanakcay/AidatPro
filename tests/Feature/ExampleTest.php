<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $this->seed();

        $user = User::where('email', 'admin@aidat.local')->firstOrFail();

        $this
            ->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Nilüfer Sitesi')
            ->assertSee('Hızlı İşlemler');
    }
}
