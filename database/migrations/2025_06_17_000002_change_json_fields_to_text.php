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
        // Change the column types in the articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->text('content_json')->nullable()->change();
            $table->text('excerpt_json')->nullable()->change();
            $table->text('summary_json')->nullable()->change();
        });

        // Change the column type in the notes table
        Schema::table('notes', function (Blueprint $table) {
            $table->text('content_json')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Change the column types back to JSON in the articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->json('content_json')->nullable()->change();
            $table->json('excerpt_json')->nullable()->change();
            $table->json('summary_json')->nullable()->change();
        });

        // Change the column type back to JSON in the notes table
        Schema::table('notes', function (Blueprint $table) {
            $table->json('content_json')->nullable()->change();
        });
    }
};
