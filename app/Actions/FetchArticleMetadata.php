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
            // Use cURL instead of file_get_contents for more robust fetching
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $html = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Get the final URL after redirects
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($html === false || !empty($error) || $httpCode >= 400) {
                Log::error("Failed to fetch URL: $url, Error: $error, HTTP Code: $httpCode");
                return [];
            }

            // Log if there was a redirect
            if ($finalUrl !== $url) {
                Log::info('URL redirected', [
                    'original_url' => $url,
                    'final_url' => $finalUrl,
                ]);
            }

            $readability = new Readability(new Configuration([
                'FixRelativeURLs' => true,
                'ArticleByline' => true
            ]));
            $readability->parse($html);

            return [
                'title' => $readability->getTitle(),
                'content' => $readability->getContent(),
                'author' => $readability->getAuthor(),
                'site_name' => $readability->getSiteName(),
                'featured_image' => $readability->getImage(),
                'excerpt' => $readability->getExcerpt(),
                'final_url' => $finalUrl, // Include the final URL after redirects
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
