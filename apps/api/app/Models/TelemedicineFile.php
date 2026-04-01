<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemedicineFile extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'file_category',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TelemedicineSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obter URL do arquivo
     */
    public function getFileUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Obter tamanho formatado
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
