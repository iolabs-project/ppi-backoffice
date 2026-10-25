<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    // Request payload for error logs, without credentials
    protected function loggableRequest(Request $request): array
    {
        return $request->except(['password', 'password_confirmation', 'current_password']);
    }
}
