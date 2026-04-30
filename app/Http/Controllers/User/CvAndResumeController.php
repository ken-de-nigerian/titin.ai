<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CvAndResumeController extends Controller
{
    public function index()
    {
        return Inertia::render('User/CvAndResume/Index');
    }
}
