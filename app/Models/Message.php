<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'channel_id',
        'sender_id',
        'content',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Usuario que envió el mensaje
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Conversación a la que pertenece
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Canal al que pertenece
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}


