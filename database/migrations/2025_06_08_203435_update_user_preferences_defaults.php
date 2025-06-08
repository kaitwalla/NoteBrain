<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UserPreference;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records to include new preferences
        UserPreference::all()->each(function ($preference) {
            $prefs = $preference->article_preferences;
            if (!isset($prefs['font_family'])) {
                $prefs['font_family'] = 'system';
            }
            if (!isset($prefs['line_height'])) {
                $prefs['line_height'] = 1.5;
            }
            $preference->article_preferences = $prefs;
            $preference->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it only adds default values
    }
};
