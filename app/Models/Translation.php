<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     schema="Translation",
 *     type="object",
 *     title="Translation",
 *     description="Translation model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="key", type="string", example="welcome.message"),
 *     @OA\Property(property="value", type="string", example="Welcome to our application"),
 *     @OA\Property(property="locale_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="locale", ref="#/components/schemas/Locale"),
 *     @OA\Property(property="tags", type="array", @OA\Items(ref="#/components/schemas/Tag"))
 * )
 */
class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'locale_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'translation_tags');
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByLocale($query, string $localeCode)
    {
        return $query->whereHas('locale', function ($q) use ($localeCode) {
            $q->where('code', $localeCode);
        });
    }

    public function scopeByTag($query, string $tagName)
    {
        return $query->whereHas('tags', function ($q) use ($tagName) {
            $q->where('name', $tagName);
        });
    }

    public function scopeSearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('key', 'like', "%{$searchTerm}%")
              ->orWhere('value', 'like', "%{$searchTerm}%");
        });
    }
}
