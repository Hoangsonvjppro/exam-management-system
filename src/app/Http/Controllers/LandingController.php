<?php

namespace App\Http\Controllers;

use App\Services\LandingPageService;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function __construct(private readonly LandingPageService $landingPageService) {}

    public function __invoke(): View
    {
        return view('welcome', $this->landingPageService->getLandingData());
    }
}
