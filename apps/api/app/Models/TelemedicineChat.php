<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemedicineChat extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'message',
        'type',
        'file_url',
        'file_name',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
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
     * Marcar mensagem como lida
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Criar mensagem de sistema
     */
    public static function createSystemMessage(int $sessionId, string $message): self
    {
        return self::create([
            'session_id' => $sessionId,
            'user_id' => null, // Sistema não tem usuário
            'message' => $message,
            'type' => 'system',
        ]);
    }
}
