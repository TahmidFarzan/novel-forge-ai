<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

use App\Http\Requests\QuizSubmitRequest;

class PageController extends Controller
{
    public function home(): InertiaResponse
    {
        return Inertia::render('Home');
    }
}
