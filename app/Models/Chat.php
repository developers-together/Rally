<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['name','team_id','type'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function perm(): HasOne
    {
        return $this->hasOne(ChatPerm::class);
    }



    // protected static function boot()
    // {
    //     parent::boot();
    //
    //     static::deleting(function ($chat) {
    //         $chat->messages()->delete(); // Delete messages when chat is deleted
    //     });
    // }

}
