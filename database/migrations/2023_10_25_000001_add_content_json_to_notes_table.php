<?php

use App\Models\Note;
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
        Schema::table('notes', function (Blueprint $table) {
            $table->json('content_json')->nullable()->after('content');
        });

        // Convert existing notes' HTML content to JSON
        $htmlConverter = new HtmlToJsonConverter();
        $notes = Note::all();

        foreach ($notes as $note) {
            $note->content_json = $htmlConverter->convert($note->content);
            $note->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('content_json');
        });
    }
};
