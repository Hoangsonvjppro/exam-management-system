<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['name', 'year', 'term', 'start_date', 'end_date', 'is_current'];

    public function courseSections()
    {
        return $this->hasMany(CourseSection::class);
    }
}
