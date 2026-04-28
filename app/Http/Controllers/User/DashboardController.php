<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('User/Dashboard');
    }
}
