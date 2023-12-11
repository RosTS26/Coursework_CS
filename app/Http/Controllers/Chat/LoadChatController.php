<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Chat\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Chat\UserChatIdRequest;

class LoadChatController extends BaseController
{
    public function __invoke(UserChatIdRequest $request) {
        $userChatId = $request->input('userChatId');
        return $this->service->getChat($userChatId);
    }
}
