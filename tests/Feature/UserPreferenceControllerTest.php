<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_updates_preferences()
    {
        $user = User::factory()->create();
        $preferences = [
            'font_size' => 1.2,
            'paragraph_spacing' => 2.0,
            'content_width' => '4xl',
            'font_family' => 'serif',
            'line_height' => 1.5,
        ];

        $response = $this->actingAs($user)
            ->postJson(route('preferences.update'), $preferences);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
        ]);

        $userPreference = UserPreference::where('user_id', $user->id)->first();
        $this->assertEquals($preferences['font_size'], $userPreference->article_preferences['font_size']);
        $this->assertEquals($preferences['paragraph_spacing'], $userPreference->article_preferences['paragraph_spacing']);
        $this->assertEquals($preferences['content_width'], $userPreference->article_preferences['content_width']);
        $this->assertEquals($preferences['font_family'], $userPreference->article_preferences['font_family']);
        $this->assertEquals($preferences['line_height'], $userPreference->article_preferences['line_height']);
    }

    public function test_update_requires_authentication()
    {
        $preferences = [
            'font_size' => 1.2,
            'paragraph_spacing' => 2.0,
            'content_width' => '4xl',
            'font_family' => 'serif',
            'line_height' => 1.5,
        ];

        $response = $this->postJson(route('preferences.update'), $preferences);

        $response->assertStatus(401);
    }

    public function test_show_returns_preferences()
    {
        $user = User::factory()->create();
        $preferences = [
            'font_size' => 1.25,
            'paragraph_spacing' => 2,
            'content_width' => '4xl',
            'font_family' => 'system',
            'line_height' => 1.5,
        ];

        UserPreference::create([
            'user_id' => $user->id,
            'article_preferences' => $preferences,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('preferences.show'));

        $response->assertStatus(200)
            ->assertJson($preferences);
    }

    public function test_show_requires_authentication()
    {
        $response = $this->getJson(route('preferences.show'));

        $response->assertStatus(401);
    }
}
