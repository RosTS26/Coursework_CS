<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Friends\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Friends\OptionRequest;

class GetFriendController extends BaseController
{
    public function __invoke(OptionRequest $request) {
        $option = $request->input('option');
        return $this->service->option($option);
    }
}
