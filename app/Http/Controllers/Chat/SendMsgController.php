<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Chat\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Chat\SendMsgRequest;

class SendMsgController extends BaseController
{
    public function __invoke(SendMsgRequest $request) {
        $userChatId = $request->input('userChatId');
        $message = $request->input('message');
        return $this->service->sendMsg($userChatId, $message);
    }
}
