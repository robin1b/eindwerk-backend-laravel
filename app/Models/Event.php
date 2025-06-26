<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'admin_name',
        'admin_code',
        'join_code',
        'name',
        'description',
        'deadline',
        'privacy',
        'goal_amount',
    ];


    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function contributions()
    {
        return $this->hasMany(\App\Models\Contribution::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
