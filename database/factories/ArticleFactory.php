<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'url' => $this->faker->url,
            'status' => Article::STATUS_INBOX,
            'author' => $this->faker->name,
            'site_name' => $this->faker->company,
            'featured_image' => $this->faker->imageUrl,
            'excerpt' => $this->faker->paragraph,
            'summary' => null,
            'summarized_at' => null,
            'read_at' => null,
            'archived_at' => null,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the article is archived.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function archived()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Article::STATUS_ARCHIVED,
                'archived_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the article has a summary.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function summarized()
    {
        return $this->state(function (array $attributes) {
            return [
                'summary' => $this->faker->paragraphs(2, true),
                'summarized_at' => now(),
            ];
        });
    }
}
