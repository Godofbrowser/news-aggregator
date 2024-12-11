<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;
    protected $fillable = ['headline', 'body', 'thumbnail', 'provider', 'provider_id', 'link'];

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
