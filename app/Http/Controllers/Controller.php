<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        // Apply activity logging middleware to all controller routes
        // so that we capture login, menu access, CRUD and logout actions.
        $this->middleware(\App\Http\Middleware\LogActivity::class);
    }
}
