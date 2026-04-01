<?php

namespace App\Http\View\Composers;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class LecturerComplaintComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $count = 0;

        if (
            ! Auth::check()
            || ! Schema::hasTable('roles')
            || ! Schema::hasTable('model_has_roles')
            || ! Schema::hasTable('complaints')
            || ! Schema::hasTable('course_sections')
        ) {
            $view->with('pendingComplaintsCount', $count);
            return;
        }

        try {
            /** @var User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->hasRole('lecturer')) {
                $view->with('pendingComplaintsCount', $count);
                return;
            }

            $lecturerId = Auth::id();
            $count = Complaint::where('status', 'pending')
                ->whereHas('section', function ($query) use ($lecturerId) {
                    $query->where('lecturer_id', $lecturerId);
                })
                ->count();
        } catch (QueryException) {
            $count = 0;
        }

        $view->with('pendingComplaintsCount', $count);
    }
}
