<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Friends\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Friends\UserNameRequest;

class AddFriendController extends BaseController
{
    public function __invoke(UserNameRequest $request) {
        $friendName = $request->input('friendName');
        return $this->service->addFriend($friendName);
    }
}
