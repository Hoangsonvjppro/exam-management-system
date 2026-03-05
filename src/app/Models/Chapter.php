<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'subject_id',
        'name',
        'order',
        'description',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
