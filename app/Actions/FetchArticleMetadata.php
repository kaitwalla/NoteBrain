<?php

namespace App\Actions;

use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Illuminate\Support\Facades\Log;

class FetchArticleMetadata
{
    /**
     * Fetch metadata for an article from a URL.
     *
     * @param string $url The URL of the article
     * @return array The article metadata
     */
    public function __invoke(string $url): array
    {
        try {
            $html = file_get_contents($url);
            if ($html === false) {
                return [];
            }

            $readability = new Readability(new Configuration());
            $readability->parse($html);

            return [
                'title' => $readability->getTitle(),
                'content' => $readability->getContent(),
                'author' => $readability->getAuthor(),
                'site_name' => $readability->getSiteName(),
                'featured_image' => $readability->getImage(),
                'excerpt' => $readability->getExcerpt(),
            ];
        } catch (ParseException $e) {
            Log::error('Failed to parse article: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch article metadata: ' . $e->getMessage());
            return [];
        }
    }
}
