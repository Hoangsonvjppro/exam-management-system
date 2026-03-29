<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\IndexQuestionMetadataRequest;
use App\Models\Difficulty;
use App\Services\QuestionMetadataQueryService;
use Illuminate\Http\JsonResponse;

class DifficultyController extends Controller
{
    public function index(IndexQuestionMetadataRequest $request, QuestionMetadataQueryService $questionMetadataQueryService): JsonResponse
    {
        $this->authorize('viewAny', Difficulty::class);

        $keyword = $request->validated()['q'] ?? null;

        return response()->json([
            'data' => $questionMetadataQueryService->getDifficulties($keyword),
        ]);
    }
}
