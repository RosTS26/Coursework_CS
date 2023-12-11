import Echo from "laravel-echo";
import Pusher from "pusher-js";
import axios from 'axios';
// Настройка csrf-токена
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
});


// Настройка Axios для отправки заголовка X-Socket-ID
window.Echo.connector.pusher.connection.bind('connected', function () {
    axios.defaults.headers.common['X-Socket-ID'] = window.Echo.socketId();
});

var userItem; // Ссылка на user_item

function defaultColorBtns() {
    $('#my-friends').css('background', 'linear-gradient(#49708f, #293f50)');
    $('#sent-app').css('background', 'linear-gradient(#49708f, #293f50)');
    $('#incoming-app').css('background', 'linear-gradient(#49708f, #293f50)');
}

// Функция для сбрасываний до стандартных значений 
function defaultProfile() {
	$('#username').html("UserName: ");
	$('#ID').html(`ID: `);
	$('.functions-btns').html('');
	$('.chat-container').html('<div class="chat-info">Select, who you would like to write to!</div>');
	$('.friend-profile').css('display', 'none');
    userChatId = 0;
	username = '';
}


// Демонстрация профиля друга
function showProfile(userChatId, username) {
	$('.friend-profile').css('display', 'flex');
	$('#username').html("UserName: <b>" + username + '</b>');
	$('#ID').html(`ID: <b>${userChatId}</b>`);
	// if (online) {
	// 	$('#ID').append('<div id="online-check">Online</div>');
	// } else {
	// 	$('#ID').append('<div id="online-check">Offline</div>');
	// }
}

// Выбор показа списков друзей (заявок)
function getUsersList(option) {
    let url = '/friends';
    axios.post(url, { option: option })
        .then(function(response) {
            let arr = response.data;

            $('.friends_list').empty();
            arr.forEach(item => {
                // Кол-во новых сообщений
                let checkNewMsg = $('<span class="check-newMsg newMsg-info-'+ item['id'] +' position-absolute translate-middle badge rounded-pill bg-danger"></span>');
                checkNewMsg.html(item['numNewMsgs']);
                if (item['numNewMsgs'] <= 0 || userChatId == item['id']) checkNewMsg.css('display', 'none');
                // Элемент списка
                let friend = $('<li></li>').addClass('user_item').attr('id', item['id']).attr('username', item['name']).html(item['name']).append(checkNewMsg);

                $('.friends_list').first().append(friend);
            });
        })
        .catch(function (error) {
            alert("Error option...\n" + error);
        });
}

// === Add friend ===
function addFriend(status, friendName) {
    let statusList = [
        'Friend request sent to "'+ friendName +'"!', // Отправлена
        'Friend request "'+ friendName +'" accepted!',  // Принята
        'User "'+ friendName +'" is already your friend!', // Уже друг
        'Friend request "'+ friendName +'" pending!', // На рассмотрении
        '"'+ friendName +'" not found!', // Не найден
        'ERROR when sending friend request!' // Ошибка
    ];

    alert(statusList[status]);
}

// === Cancel (reject) or delete app ===
function cancelOrDelete (status) {
    let statusList = [
        'Error operation!',
        'No friend request!',
        'Friend request successfully canceled!',
    ];

    alert(statusList[status]);
}

// Оповещение о кол-ве заявок в друзья 
function numIncomApp() {
    let num = Number($('.numIncom').html());
    num--;
    $('.numIncom').html(num);

    if (num <= 0) $('.numIncom').css('display', 'none');

    // Если открыт список входящих заявок, тогда удаляем текущую заявку
    if ($('#option3').is(':checked')) userItem.remove();
}

$(function() {
    // Панель для выбора друга
	$('.friends_list').on('click', '.user_item', function() {

        userItem = $(this); // Сохраняем ссылку на .user_item
		$(this).prependTo('.friends_list');
		$('.friends_info').scrollTop(0);
		$('.user_item').css('background', '#F0F0F0');
		$(this).css('background', '#46EF7F');
		$('.chat-container').html('');

        // === My friends ===
		if ($('#option1').is(':checked')) {
            userChatId = Number($(this).attr('id'));
		    username = $(this).attr('username');

            showProfile(userChatId, username);

			$('.functions-btns').html('<input type="button" id="delete-friend" value="Delete a friend">' +
                '<input type="button" id="delete-chat" value="Delete chat">');
            $('#btnSendMsg').prop('disabled', false);

            // === Удалить друга из друзей ===
			$('#delete-friend').on('click', function() {
				if (confirm('Are you sure you want to delete friend "'+ username +'" ?')) {
					let url = '/delete-friend';
            
                    axios.post(url, { user_id: userChatId })
                        .then(function(response) {

                            let statusList = [
                                'Error operation!',
                                'No friend request!',
                                '"'+ username +'" is not a friend!',
                                'Friend "'+ username +'" removed from friends list!',
                            ];
                            alert(statusList[response.data]);

                            userItem.remove();
                            defaultProfile();
                        })
                        .catch(function (error) {
                            alert("Error delete friend...\n" + error);
                        });
				}
			});

            // === Удаление чата с другом === 
            $('#delete-chat').on('click', function() {
                if (confirm('Are you sure you want to delete this chat?')) {
                    let url = '/delete-chat';

                    axios.post(url, { userChatId: userChatId })
                        .then(function(res) {
                            if (res.data == 0) {
                                $('.chat-container').html('<div class="chat-info">Send a message first!</div>');
                            } else throw 'User not found!';
                        })
                        .catch(function (error) {
                            alert("Error delete chat...\n" + error);
                        });
                }
            });
        }

        // === Sent app ===
		else if ($('#option2').is(':checked')) {
			userChatId = Number($(this).attr('id'));
		    username = $(this).attr('username');

            showProfile(userChatId, username);

            $('.functions-btns').html('<input type="button" id="cancel-app" value="Cancel app">');
            $('.chat-container').html('<div class="chat-info">Wait until your friend request is confirm to start chatting!</div>');

			$('#cancel-app').on('click', function() {
				if (confirm('Are you sure you want to cancel the application?')) {
					let url = '/cancel-app';

                    axios.post(url, { user_id: userChatId })
                        .then(function(response) {
                            cancelOrDelete(response.data);
                            userItem.remove();
                            defaultProfile();
                        })
                        .catch(function (error) {
                            alert("Error cancel the application...\n" + error);
                        });
				}
			});
		}

        // === Incoming app ===
		else if ($('#option3').is(':checked')) {
			userChatId = Number($(this).attr('id'));
		    username = $(this).attr('username');

            showProfile(userChatId, username);

			$('.functions-btns').html('<input type="button" id="accept-app" value="Accept app">');
			$('.functions-btns').append('<input type="button" id="cancel-app" value="Cancel app">');
            $('.chat-container').html('<div class="chat-info">Accept the request to start chatting!</div>');

            // Подтвердение заявки
			$('#accept-app').on('click', function() {
				if (confirm('Do you want to accept friend request?')) {
					let url = '/add-friend';

                    axios.post(url, { friendName: username })
                        .then(function(response) {
                            addFriend(response.data, username);
                            numIncomApp();
                            defaultProfile();
                        })
                        .catch(function (error) {
                            alert("Error add friend...\n" + error);
                        });
				}
			});

			// Удаление входящей заявки
			$('#cancel-app').on('click', function() {
				if (confirm('Are you sure you want to cancel the application?')) {
					let url = '/reject-app';

                    axios.post(url, { user_id: userChatId })
                        .then(function(response) {
                            cancelOrDelete(response.data);
                            numIncomApp();
                            defaultProfile();
                        })
                        .catch(function (error) {
                            alert("Error cancel the application...\n" + error);
                        });
				}
			});
		}
    });

    // Добавление друга
	$('#friend-add').on('click', function() {
		if ($('#friend-name').val().trim() !== '') {
			let url = '/add-friend';
			let friendName = $('#friend-name').val();

            axios.post(url, { friendName: friendName })
                .then(function(response) {
                    addFriend(response.data, friendName);
                    if (response.data == 1) numIncomApp();
                })
                .catch(function (error) {
                    alert("Error add friend...\n" + error);
                });
            }
	});

	// Мои друзья
	$('#my-friends').on('click', function() {
		defaultColorBtns();
        getUsersList('friends')
		$('#my-friends').css('background', 'linear-gradient(#49B681, #298533)');
	}); 

	// Отправленные заявки
	$('#sent-app').on('click', function() {
		defaultColorBtns();
        getUsersList('sentApp')
		$('#sent-app').css('background', 'linear-gradient(#49B681, #298533)');
	}); 

	// Полученные заявки
	$('#incoming-app').on('click', function() {
		defaultColorBtns();
        getUsersList('incomApp')
		$('#incoming-app').css('background', 'linear-gradient(#49B681, #298533)');
	});

	$('#my-friends').trigger('click');
});