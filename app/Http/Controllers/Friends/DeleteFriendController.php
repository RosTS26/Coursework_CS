<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Friends\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Friends\UserIdRequest;
use App\Models\User;

class DeleteFriendController extends BaseController
{
    public function __invoke(UserIdRequest $request) {
        $user_id = $request->input('user_id');
        $userDB = User::find($user_id);
        if (!$userDB) return 1;

        return $this->service->deleteFriend($userDB);
    }
}