<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegraphMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegraph_chat_id',
        'message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(TelegraphChat::class, 'telegraph_chat_id');
    }
}
