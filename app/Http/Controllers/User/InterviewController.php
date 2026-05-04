<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class InterviewController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('User/Interview/Index');
    }
}
