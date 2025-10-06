<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $table = 'channels';

    protected $fillable = [
        'name',
        'slug',
        'subject_id',
        'members_count',
    ];

    /**
     * Materia asociada
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Mensajes del canal
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


