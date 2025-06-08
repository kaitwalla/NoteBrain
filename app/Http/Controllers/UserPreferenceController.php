<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserPreference;

class UserPreferenceController extends Controller
{
    public function update(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                Log::error('No authenticated user found');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $preferences = UserPreference::firstOrCreate(
                ['user_id' => $user->id],
                ['article_preferences' => []]
            );
            
            // Update article preferences
            $preferences->article_preferences = array_merge(
                $preferences->article_preferences ?? [],
                $request->only([
                    'font_size',
                    'paragraph_spacing',
                    'content_width',
                    'font_family',
                    'line_height'
                ])
            );
            
            $preferences->save();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error updating preferences: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
} 