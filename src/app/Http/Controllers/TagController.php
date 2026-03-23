<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\IndexQuestionMetadataRequest;
use App\Models\Tag;
use App\Services\QuestionMetadataQueryService;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function index(IndexQuestionMetadataRequest $request, QuestionMetadataQueryService $questionMetadataQueryService): JsonResponse
    {
        $this->authorize('viewAny', Tag::class);

        $keyword = $request->validated()['q'] ?? null;

        return response()->json([
            'data' => $questionMetadataQueryService->getTags($keyword),
        ]);
    }
}
