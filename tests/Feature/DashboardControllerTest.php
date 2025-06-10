<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_dashboard()
    {
        $user = User::factory()->create();
        $inboxArticle = Article::factory()->create(['user_id' => $user->id, 'status' => 'inbox']);
        $archivedArticle = Article::factory()->create(['user_id' => $user->id, 'status' => 'archived']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('inboxCount', 1);
        $response->assertViewHas('archivedCount', 1);
        $response->assertViewHas('recentArticles');
        $response->assertSee($inboxArticle->title);
    }

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_only_user_articles()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1Article = Article::factory()->create(['user_id' => $user1->id, 'status' => 'inbox']);
        $user2Article = Article::factory()->create(['user_id' => $user2->id, 'status' => 'inbox']);

        $response = $this->actingAs($user1)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('inboxCount', 1);
        $response->assertSee($user1Article->title);
        $response->assertDontSee($user2Article->title);
    }
}
