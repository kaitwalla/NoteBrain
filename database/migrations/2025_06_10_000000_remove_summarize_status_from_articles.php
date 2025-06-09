<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update all articles with 'summarize' status to 'inbox'
        DB::table('articles')->where('status', 'summarize')->update(['status' => 'inbox']);

        // For MySQL, we need to modify the enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('inbox', 'unread', 'read', 'archived') NOT NULL DEFAULT 'inbox'");
        }
        // For SQLite (which doesn't support enum), we don't need to do anything else
        // since we've already updated the status values
    }

    public function down()
    {
        // For MySQL, add 'summarize' back to the enum
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE articles MODIFY COLUMN status ENUM('inbox', 'unread', 'read', 'archived', 'summarize') NOT NULL DEFAULT 'inbox'");
        }
        // For SQLite, we don't need to do anything since it doesn't use enums
    }
};
