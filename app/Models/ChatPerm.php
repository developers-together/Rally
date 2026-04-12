<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatPerm extends Model
{
    use HasFactory;

    protected $table = 'chat_permissions';

    protected $casts = [
        'notify' => 'boolean',
        'allow_ai' => 'boolean',
    ];

    protected $fillable = ['chat_id', 'visibility', 'write', 'delete', 'modify', 'notify', 'allow_ai'];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
