<?php

namespace App\Http\Controllers\Friends;

use App\Services\Friends\Service;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}