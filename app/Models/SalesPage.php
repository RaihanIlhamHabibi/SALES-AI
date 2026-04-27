<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SalesPage extends Model
{
    protected $fillable = [
        'user_id',
        'product_name',
        'description',
        'features',
        'target_audience',
        'price',
        'unique_selling_points',
        'generation',
        'llm_provider',
        'llm_model',
    ];

    protected $casts = [
        'features' => 'array',
        'generation' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

