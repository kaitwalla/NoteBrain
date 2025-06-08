<?php

namespace App\Actions;

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\User;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SaveArticle
{
    /**
     * Save an article from a URL.
     *
     * @param string $url The URL of the article to save
     * @param User $user The user saving the article
     * @return Article|null The saved article or null if failed
     */
    public function __invoke(string $url, User $user): ?Article
    {
        try {
            // Fetch the HTML content
            $response = Http::get($url);
            if (!$response->successful()) {
                Log::error('Failed to fetch article', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $html = $response->body();

            // Configure Readability
            $configuration = new Configuration();
            $configuration
                ->setFixRelativeURLs(true)
                ->setOriginalURL($url)
                ->setArticleByline(true)
                ->setCleanConditionally(true);

            // Parse the content
            $readability = new Readability($configuration);
            $readability->parse($html);

            // Create the article
            $article = Article::create([
                'user_id' => $user->id,
                'url' => $url,
                'title' => $readability->getTitle(),
                'content' => $readability->getContent(),
                'excerpt' => $readability->getExcerpt(),
                'featured_image' => $readability->getImage(),
                'author' => $readability->getAuthor(),
                'site_name' => parse_url($url, PHP_URL_HOST),
                'status' => 'inbox',
            ]);

            // Extract and download images
            $this->processImages($article, $readability->getContent());

            return $article;

        } catch (ParseException $e) {
            Log::error('Failed to parse article', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Unexpected error saving article', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function processImages(Article $article, string $content): void
    {
        // Find all image tags in the content
        preg_match_all('/<img[^>]+src="([^">]+)"/', $content, $matches);
        
        if (empty($matches[1])) {
            return;
        }

        foreach ($matches[1] as $imageUrl) {
            try {
                // Skip data URLs and invalid URLs
                if (str_starts_with($imageUrl, 'data:') || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                    continue;
                }

                // Download the image
                $response = Http::get($imageUrl);
                if (!$response->successful()) {
                    continue;
                }

                // Get image info
                $mimeType = $response->header('Content-Type');
                if (!str_starts_with($mimeType, 'image/')) {
                    continue;
                }

                // Generate a unique filename
                $extension = Str::after($mimeType, 'image/');
                $filename = 'articles/' . $article->id . '/' . Str::random(40) . '.' . $extension;
                
                // Store the image
                Storage::disk('public')->put($filename, $response->body());

                // Create the image record
                ArticleImage::create([
                    'article_id' => $article->id,
                    'original_url' => $imageUrl,
                    'local_path' => $filename,
                    'mime_type' => $mimeType,
                ]);

                // Replace the image URL in the content
                $article->content = str_replace(
                    $imageUrl,
                    Storage::disk('public')->url($filename),
                    $article->content
                );
            } catch (\Exception $e) {
                // Log the error but continue processing other images
                \Log::error('Failed to process image: ' . $imageUrl, [
                    'error' => $e->getMessage(),
                    'article_id' => $article->id
                ]);
            }
        }

        // Save the updated content with local image URLs
        $article->save();
    }
} 