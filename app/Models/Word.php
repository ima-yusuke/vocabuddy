<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'part_of_speech',
        'pronunciation_katakana',
    ];

    public function japanese()
    {
        return $this->hasMany(Japanese::class);
    }

}
