<?php

use App\Models\Article;
use App\Services\HtmlToJsonConverter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->json('content_json')->nullable()->after('content');
            $table->json('excerpt_json')->nullable()->after('excerpt');
            $table->json('summary_json')->nullable()->after('summary');
        });

        // Convert existing articles' HTML content to JSON
        $htmlConverter = new HtmlToJsonConverter();
        $articles = Article::all();

        foreach ($articles as $article) {
            $article->content_json = $htmlConverter->convert($article->content);
            $article->excerpt_json = $htmlConverter->convert($article->excerpt);
            $article->summary_json = $htmlConverter->convert($article->summary);
            $article->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('content_json');
            $table->dropColumn('excerpt_json');
            $table->dropColumn('summary_json');
        });
    }
};
