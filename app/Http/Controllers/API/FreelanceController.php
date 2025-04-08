<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FreelanceService;
use Illuminate\Http\JsonResponse;

class FreelanceController extends Controller
{
    public function __construct(protected FreelanceService $freelanceService) {}

    public function index(): JsonResponse
    {
        $jobs = $this->freelanceService->getJobs();
        return response()->json($jobs);
    }
}
