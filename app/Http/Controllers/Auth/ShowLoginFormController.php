<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

final class ShowLoginFormController extends Controller
{
    public function __invoke()
    {
        return view('auth.login');
    }
}
