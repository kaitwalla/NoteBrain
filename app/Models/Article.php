<?php

namespace App\Models;

use App\Services\HtmlToJsonConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function ($article) {
            // Convert HTML content to JSON arrays on save
            $htmlConverter = app(HtmlToJsonConverter::class);

            // Only convert if the content has changed or content_json is null
            if ($article->isDirty('content') || is_null($article->content_json)) {
                $article->content_json = $htmlConverter->convert($article->content);
            }

            // Only convert if the excerpt has changed or excerpt_json is null
            if ($article->isDirty('excerpt') || is_null($article->excerpt_json)) {
                $article->excerpt_json = $htmlConverter->convert($article->excerpt);
            }

            // Only convert if the summary has changed or summary_json is null
            if ($article->isDirty('summary') || is_null($article->summary_json)) {
                $article->summary_json = $htmlConverter->convert($article->summary);
            }
        });
    }

    const STATUS_INBOX = 'inbox';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_DELETED = 'deleted';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'content_json',
        'url',
        'status',
        'starred',
        'author',
        'site_name',
        'featured_image',
        'excerpt',
        'excerpt_json',
        'google_drive_file_id',
        'summary',
        'summary_json',
        'summarized_at',
        'read_at',
        'archived_at',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'starred' => 'boolean',
        'content_json' => 'array',
        'excerpt_json' => 'array',
        'summary_json' => 'array',
    ];

    /**
     * Get the user that owns the article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Archive the article.
     */
    public function archive(): void
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    /**
     * Move the article to inbox.
     */
    public function moveToInbox(): void
    {
        $this->update([
            'status' => self::STATUS_INBOX,
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
     * Scope a query to only include archived articles.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope a query to only include inbox articles.
     */
    public function scopeInbox($query)
    {
        return $query->where('status', self::STATUS_INBOX);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class);
    }

    /**
     * Star the article.
     */
    public function star(): void
    {
        $this->update([
            'starred' => true,
        ]);
    }

    /**
     * Unstar the article.
     */
    public function unstar(): void
    {
        $this->update([
            'starred' => false,
        ]);
    }

    /**
     * Scope a query to only include starred articles.
     */
    public function scopeStarred($query)
    {
        return $query->where('starred', true);
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_INBOX => 'Inbox',
            self::STATUS_ARCHIVED => 'Archived',
            self::STATUS_DELETED => 'Deleted',
        ];
    }
}
