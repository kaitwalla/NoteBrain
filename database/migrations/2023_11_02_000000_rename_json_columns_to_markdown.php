<?php

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
        // Rename columns in the articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('content_json', 'content_markdown');
            $table->renameColumn('excerpt_json', 'excerpt_markdown');
            $table->renameColumn('summary_json', 'summary_markdown');
        });

        // Rename column in the notes table
        Schema::table('notes', function (Blueprint $table) {
            $table->renameColumn('content_json', 'content_markdown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename columns back in the articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->renameColumn('content_markdown', 'content_json');
            $table->renameColumn('excerpt_markdown', 'excerpt_json');
            $table->renameColumn('summary_markdown', 'summary_json');
        });

        // Rename column back in the notes table
        Schema::table('notes', function (Blueprint $table) {
            $table->renameColumn('content_markdown', 'content_json');
        });
    }
};
