<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestProofImageController extends Controller
{
    public function show(Request $request, LeaveRequest $leaveRequest): StreamedResponse
    {
        $user = $request->user();

        abort_if(!$user, 403);

        $leaveRequest->loadMissing('courseSection');

        $isOwner = (int) $user->getAuthIdentifier() === (int) $leaveRequest->student_id;
        $canManageSection = $leaveRequest->courseSection && $user->can('manage', $leaveRequest->courseSection);

        abort_unless($isOwner || $canManageSection, 403);

        $path = $leaveRequest->normalizedProofImagePath();
        $storage = Storage::disk('public');

        if (!$path) {
            abort(404);
        }

        if (!$storage->exists($path)) {
            $fallbackCandidates = [
                'leave-proofs/'.ltrim($path, '/'),
                'leave-proofs/'.basename($path),
                basename($path),
            ];

            $resolved = collect($fallbackCandidates)
                ->unique()
                ->first(fn(string $candidate) => $candidate !== '' && $storage->exists($candidate));

            if (!$resolved) {
                abort(404);
            }

            $path = $resolved;
        }

        return $storage->response($path, null, [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Disposition' => 'inline',
        ]);
    }
}
