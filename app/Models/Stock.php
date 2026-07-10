<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'symbol',
        'name',
        'logo',
        'exchange',
        'finnhubIndustry', // Meant for the most popular industry section, currently not in use
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
