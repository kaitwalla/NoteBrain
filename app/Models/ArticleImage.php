<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleImage extends Model
{
    protected $fillable = [
        'article_id',
        'original_url',
        'local_path',
        'mime_type',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function getLocalUrlAttribute(): string
    {
        return asset('storage/' . $this->local_path);
    }
} 