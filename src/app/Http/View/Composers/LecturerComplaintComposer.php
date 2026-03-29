<?php

namespace App\Http\View\Composers;

use App\Models\Complaint;
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

        if (Auth::check() && Auth::user()->hasRole('lecturer')) {
            $lecturerId = Auth::id();
            $count = Complaint::where('status', 'pending')
                ->whereHas('section', function ($query) use ($lecturerId) {
                    $query->where('lecturer_id', $lecturerId);
                })
                ->count();
        }

        $view->with('pendingComplaintsCount', $count);
    }
}
