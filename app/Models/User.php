<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_drive_folder_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferences' => 'array',
    ];

    protected $with = ['preferences'];

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function getArticlePreferences()
    {
        return $this->preferences?->article_preferences ?? [
            'font_size' => 1.25,
            'paragraph_spacing' => 2,
            'content_width' => '4xl',
            'font_family' => 'system',
            'line_height' => 1.5,
        ];
    }

    /**
     * Check if the user has connected their Google Drive account.
     *
     * @return bool
     */
    public function hasGoogleDriveToken(): bool
    {
        return !is_null($this->google_access_token) && !is_null($this->google_refresh_token);
    }

    /**
     * Check if the user's Google Drive token is expired.
     *
     * @return bool
     */
    public function isGoogleDriveTokenExpired(): bool
    {
        if (!$this->hasGoogleDriveToken()) {
            return true;
        }

        return $this->google_token_expires_at && now()->isAfter($this->google_token_expires_at);
    }
}
