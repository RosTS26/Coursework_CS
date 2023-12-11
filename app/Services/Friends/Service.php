<?php

namespace App\Services\Friends;

use App\Models\User;
use App\Models\Friend;
use App\Models\Chat;
use Illuminate\Support\Facades\DB; // Подключение транзакций
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Events\NewFriendEvent;

class Service
{   
    // Метод возвращающий информацию о пользователях в БД "friends"
    private function getFriendsInfo($users) {
        $friendsData = [];
        foreach ($users as $item) {
            $friendDB = User::find($item->id);
            $friendlyChat = Chat::where('user_id', auth()->user()->id)->where('friend_id', $item->id)->first();
            if ($friendlyChat) $numNewMsgs = count(json_decode($friendlyChat->new_msgs));
            else $numNewMsgs = 0;

            if ($friendDB) {
                $itemData = [
                    'id' => $friendDB->id,
                    'name' => $friendDB->name,
                    'numNewMsgs' => $numNewMsgs,
                ];

                array_push($friendsData, $itemData);
            }
        }

        return $friendsData;
    }

    // Создание двух общих чатов для пользователей, которые в друзьях
    private function createChats() {

    }

    // ====================================================================

    // Метод для выбраной опции (мои друзья, отправленные заявки, входящие заявки)
    public function option($option) {
        // Получаем данные о друзьях
        $friendDB = auth()->user()->friend;

        // Отдаем клиенту информацию, которую он запросил
        switch ($option) {
            case 'friends':
                return $this->getFriendsInfo(json_decode($friendDB->friends));
            case 'sentApp':
                return $this->getFriendsInfo(json_decode($friendDB->sent_app));
            case 'incomApp':
                return $this->getFriendsInfo(json_decode($friendDB->incoming_app));
            default:
                return 'error';
        }
    }

    // Добавление в друзья
    public function addFriend($friendName) {
        // Проверка, сущесвует ли пользователь
        $friend = User::where('name', $friendName)->first();
        if (!$friend || $friendName == auth()->user()->name) return 4; // Юзер не найден

        $friendDB = auth()->user()->friend; // БД клиента

        // БД текущего пользователя
        $friends = json_decode($friendDB->friends);
        $sentApp = json_decode($friendDB->sent_app);
        $incomApp = json_decode($friendDB->incoming_app);

        // БД друга (второго пользователя)
        $hisFriends = json_decode($friend->friend->friends);
        $hisSentApp = json_decode($friend->friend->sent_app);
        $hisIncomApp = json_decode($friend->friend->incoming_app);

        try {
            // Проверяем, является ли пользователь другом
            foreach ($friends as $item) {
                if ($item->id == $friend->id) return 2;
            }

            // Проверяем, была ли заявка уже отправлена
            foreach ($sentApp as $item) {
                if ($item->id == $friend->id) return 3;
            }
            
            // Проверяем, была ли отправлена заявка от пользователя
            // Если да, то принимаем эту заявку в друзья
            foreach ($incomApp as $key => $item) {
                if ($item->id == $friend->id) {
                    // БД текущего клиента
                    unset($incomApp[$key]); // Удаляем входящую заявку
                    array_push($friends, ['id' => $friend->id]); // Добавляем пользователя в друзья
                    
                    Db::beginTransaction();
                    $friendDB->update([
                        'friends' => json_encode($friends),
                        'incoming_app' => json_encode(array_values($incomApp)),
                    ]);
                    
                    // БД отправителя заявки
                    foreach ($hisSentApp as $key2 => $item2) {
                        if ($item2->id == auth()->user()->id) {
                            unset($hisSentApp[$key2]);
                            array_push($hisFriends, ['id' => auth()->user()->id]);
                            $friend->friend->update([
                                'friends' => json_encode($hisFriends),
                                'sent_app' => json_encode(array_values($hisSentApp)),
                            ]);
                        }
                    }

                    // Создание двух чатов, если они отсутсвуют
                    // Чат пользователя с другом
                    Chat::firstOrCreate(
                        ['user_id' => auth()->user()->id, 'friend_id' => $friend->id],
                        ['chat' => json_encode([]), 'new_msgs' => json_encode([])]
                    );
                    // Копия чата для друга
                    Chat::firstOrCreate(
                        ['user_id' => $friend->id, 'friend_id' => auth()->user()->id],
                        ['chat' => json_encode([]), 'new_msgs' => json_encode([])]
                    );

                    Db::commit();
                    return 1; // Принимаем заявку
                }
            }

            // Отправляем первыми заявку в друзья
            array_push($sentApp, ['id' => $friend->id]);
            array_push($hisIncomApp, ['id' => auth()->user()->id]);

            Db::beginTransaction();
            $friendDB->update([
                'sent_app' => json_encode($sentApp),
            ]);

            $friend->friend->update([
                'incoming_app' => json_encode($hisIncomApp),
            ]);
            Db::commit();
            
        } catch (\Exception $e) {
            Db::rollBack();
            // return $e->getMessage();
            return 5; // Ошибка
        }

        // Вызов события отправки заявки в друзья по Web-Socket
        broadcast(new NewFriendEvent(auth()->user(), $friend->id))->toOthers();
        return 0; // Отправляем заявку
    }

    // Отмена заявки в друзья
    // Метод для отмены sent и incoming заявок одновременно
    public function cancelApp(User $forSentDB, User $forIncomDB) {

        $sentApp = json_decode($forSentDB->friend->sent_app);
        $incomApp = json_decode($forIncomDB->friend->incoming_app);

        try {
            foreach ($sentApp as $key => $item) {
                // Если заявка в друзья присутсвует, удаляем ее
                if ($item->id == $forIncomDB->id) {
                    unset($sentApp[$key]);

                    Db::beginTransaction();
                    $forSentDB->friend->update([
                        'sent_app' => json_encode(array_values($sentApp)),
                    ]);
    
                    foreach ($incomApp as $key2 => $item2) {
                        if ($item2->id == $forSentDB->id) {
                            unset($incomApp[$key2]);
                            $forIncomDB->friend->update([
                                'incoming_app' => json_encode(array_values($incomApp)),
                            ]);
                        }
                    }
                    
                    Db::commit();
                    return 2; // Операция успешна
                }
            }
        } catch (\Exception $e) {
            Db::rollBack();
            // return $e->getMessage();
            return 0; // error
        }

        return 1; // Заявки нету
    }

    // Удалить из друзей
    public function deleteFriend($userDB) {
        $myFriendsDB = auth()->user()->friend;
        $hisFriendsDB = $userDB->friend;

        $myFriends = json_decode($myFriendsDB->friends);
        $hisFriends = json_decode($hisFriendsDB->friends);

        try {
            // Удаляем друга из друзей (если он есть в друзьях)
            foreach ($myFriends as $key => $item) {
                if ($item->id == $userDB->id) {
                    unset($myFriends[$key]);

                    Db::beginTransaction();
                    $myFriendsDB->update([
                        'friends' => json_encode(array_values($myFriends)),
                    ]);

                    foreach ($hisFriends as $key2 => $item2) {
                        if ($item2->id == auth()->user()->id) {
                            unset($hisFriends[$key2]);

                            $hisFriendsDB->update([
                                'friends' => json_encode(array_values($hisFriends)),
                            ]);
                        }
                    }
                    
                    Db::commit();
                    return 3; // Операция успешна
                }
            }

            return 2; // Пользователя в друзьях нету

        } catch (\Exception $e) {
            Db::rollBack();
            //return $e->getMessage();
            return 0;
        }
    }
}