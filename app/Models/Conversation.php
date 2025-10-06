<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'user1_id',
        'user2_id',
    ];

    /**
     * Usuario 1
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Usuario 2
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Mensajes de la conversación
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Último mensaje
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }
}


