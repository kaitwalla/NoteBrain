<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For MySQL, we need to modify the enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('inbox', 'unread', 'read', 'archived', 'summarize') NOT NULL DEFAULT 'inbox'");
        }
        // For SQLite (which doesn't support enum), we need a different approach
        else {
            // Create a new temporary table with the updated schema
            Schema::create('articles_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('url');
                $table->string('title');
                $table->text('content');
                $table->text('excerpt')->nullable();
                $table->string('featured_image')->nullable();
                $table->string('author')->nullable();
                $table->string('site_name')->nullable();
                $table->string('status')->default('inbox'); // Use string instead of enum
                $table->timestamp('read_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamp('summarized_at')->nullable();
                $table->text('summary')->nullable();
                $table->timestamps();

                // Add index for faster queries
                $table->index(['user_id', 'status']);
                $table->index('created_at');
            });

            // Copy data from the old table to the new one
            DB::statement('INSERT INTO articles_new SELECT * FROM articles');

            // Drop the old table
            Schema::drop('articles');

            // Rename the new table to the original name
            Schema::rename('articles_new', 'articles');
        }
    }

    public function down()
    {
        // For MySQL, revert the enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('inbox', 'unread', 'read', 'archived') NOT NULL DEFAULT 'inbox'");
        }
        // For SQLite, we need to recreate the table
        else {
            // Create a new temporary table with the original schema
            Schema::create('articles_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('url');
                $table->string('title');
                $table->text('content');
                $table->text('excerpt')->nullable();
                $table->string('featured_image')->nullable();
                $table->string('author')->nullable();
                $table->string('site_name')->nullable();
                $table->string('status')->default('inbox'); // Use string instead of enum
                $table->timestamp('read_at')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamp('summarized_at')->nullable();
                $table->text('summary')->nullable();
                $table->timestamps();

                // Add index for faster queries
                $table->index(['user_id', 'status']);
                $table->index('created_at');
            });

            // Copy data from the old table to the new one, converting 'summarize' to 'inbox'
            DB::statement("INSERT INTO articles_new SELECT id, user_id, url, title, content, excerpt, featured_image, author, site_name, CASE WHEN status = 'summarize' THEN 'inbox' ELSE status END, read_at, archived_at, summarized_at, summary, created_at, updated_at FROM articles");

            // Drop the old table
            Schema::drop('articles');

            // Rename the new table to the original name
            Schema::rename('articles_new', 'articles');
        }
    }
};
