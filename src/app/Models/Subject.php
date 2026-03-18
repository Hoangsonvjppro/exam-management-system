<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'credits',
        'department',
        'description',
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function courseSections()
    {
        return $this->hasMany(CourseSection::class);
    }
}