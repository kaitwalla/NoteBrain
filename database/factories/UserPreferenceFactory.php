<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'article_preferences' => [
                'font_size' => 1.0,
                'paragraph_spacing' => 2.0,
                'content_width' => '4xl',
                'font_family' => 'system',
                'line_height' => 1.5,
            ],
        ];
    }
}
