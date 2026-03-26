<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplyUsageLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * テンプレートとのリレーション
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ReplyTemplate::class, 'template_id');
    }
}
