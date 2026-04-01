<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    protected $fillable = [
        'code',
        'description',
        'full_description',
        'category',
        'subcategory',
        'is_active',
        'synonyms',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'synonyms' => 'array',
    ];

    /**
     * Pesquisar códigos CID-10
     */
    public static function search(string $query): array
    {
        return self::where('code', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhereRaw('JSON_SEARCH(synonyms, "one", ?)', ["%{$query}%"])
            ->limit(20)
            ->get()
            ->toArray();
    }
}
