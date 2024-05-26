<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyIntake extends Model
{
    use HasFactory;

    protected $guarded = [
        "id"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
