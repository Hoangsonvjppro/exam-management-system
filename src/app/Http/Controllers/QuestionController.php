<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\IndexQuestionRequest;
use App\Models\Question;
use App\Services\QuestionBankQueryService;
use Illuminate\Contracts\View\View;

class QuestionController extends Controller
{
    public function index(IndexQuestionRequest $request, QuestionBankQueryService $questionBankQueryService): View
    {
        $this->authorize('viewAny', Question::class);

        $viewData = $questionBankQueryService->getIndexData($request->validated());

        return view('question.index', $viewData);
    }
}
