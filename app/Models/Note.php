<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    use HasFactory;

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
        'status',
        'starred',
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
        'archived_at' => 'datetime',
        'starred' => 'boolean',
    ];

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Archive the note.
     */
    public function archive(): void
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    /**
     * Move the note to inbox.
     */
    public function moveToInbox(): void
    {
        $this->update([
            'status' => self::STATUS_INBOX,
            'archived_at' => null,
        ]);
    }

    /**
     * Scope a query to only include notes with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include archived notes.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope a query to only include inbox notes.
     */
    public function scopeInbox($query)
    {
        return $query->where('status', self::STATUS_INBOX);
    }

    /**
     * Star the note.
     */
    public function star(): void
    {
        $this->update([
            'starred' => true,
        ]);
    }

    /**
     * Unstar the note.
     */
    public function unstar(): void
    {
        $this->update([
            'starred' => false,
        ]);
    }

    /**
     * Scope a query to only include starred notes.
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
