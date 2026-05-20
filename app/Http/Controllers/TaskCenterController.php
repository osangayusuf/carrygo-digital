<?php

namespace App\Http\Controllers;

use App\Services\TaskCenterPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskCenterController extends Controller
{
    public function index(Request $request, TaskCenterPageService $page): Response
    {
        return Inertia::render('TaskCenter/Index', $page->buildProps($request->user()));
    }
}
