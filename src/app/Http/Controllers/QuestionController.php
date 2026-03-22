<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function getSubjects()
    {
        $subjects = Subject::all();
        $chapters = [];
        $code = request()->input('sub-sel-ques');
        if ($code != null) {
            $subject = Subject::where('code', $code)->first();
            $chapters = Chapter::where('subject_id', $subject->id)->get();
        }
        return view('question.index', compact('subjects', 'chapters'));
    }
}
