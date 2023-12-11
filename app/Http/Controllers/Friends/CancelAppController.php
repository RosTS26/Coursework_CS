<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Friends\BaseController;
use Illuminate\Http\Request;
use App\Http\Requests\Friends\UserIdRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Отмена своей заявки в ДР
class CancelAppController extends BaseController
{
    public function __invoke(UserIdRequest $request) {
        $user_id = $request->input('user_id');
        $myDB = auth()->user();
        $hisDB = User::find($user_id);
        if (!$hisDB) return 2;

        return $this->service->cancelApp($myDB, $hisDB);
    }
}
