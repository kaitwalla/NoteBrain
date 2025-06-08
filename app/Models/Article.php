<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'url',
        'title',
        'content',
        'excerpt',
        'featured_image',
        'author',
        'site_name',
        'status',
        'read_at',
        'archived_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Get the user that owns the article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark the article as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Mark the article as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    /**
     * Archive the article.
     */
    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    /**
     * Move the article to inbox.
     */
    public function moveToInbox(): void
    {
        $this->update([
            'status' => 'inbox',
            'archived_at' => null,
        ]);
    }

    /**
     * Scope a query to only include articles with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include unread articles.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope a query to only include read articles.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope a query to only include archived articles.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include inbox articles.
     */
    public function scopeInbox($query)
    {
        return $query->where('status', 'inbox');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class);
    }
}
