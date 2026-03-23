<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\IndexQuestionMetadataRequest;
use App\Models\QuestionType;
use App\Services\QuestionMetadataQueryService;
use Illuminate\Http\JsonResponse;

class QuestionTypeController extends Controller
{
    public function index(IndexQuestionMetadataRequest $request, QuestionMetadataQueryService $questionMetadataQueryService): JsonResponse
    {
        $this->authorize('viewAny', QuestionType::class);

        $keyword = $request->validated()['q'] ?? null;

        return response()->json([
            'data' => $questionMetadataQueryService->getQuestionTypes($keyword),
        ]);
    }
}
