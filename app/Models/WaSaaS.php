<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaSaaS extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'wa_saass';
    // public function Patient(): BelongsTo
    // {
    //     return $this->belongsTo(Patient::class);
    // }
}
