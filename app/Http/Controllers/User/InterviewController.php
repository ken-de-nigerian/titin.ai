<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

final class InterviewController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Interview/Index');
    }
}
