
let myProfileId = myId;

// Увеличение блока для ввода сообщения (display: flex)
function autoExpand(textarea) {
    textarea.style.height = '39px';
    // Меняем высоту если она не привышает 124px
    if (textarea.scrollHeight <= 124) textarea.style.height = textarea.scrollHeight + 'px';
    else textarea.style.height = '124px';
}

// Отрисовка сообщений 
function getMsgs(msgs) {
    // Выводим на экран сообщения в зависимости от отправителя
    msgs.forEach(item => {
        let msg = $('<div></div>');

        if (Number(item['id']) === userChatId) msg.addClass('message received-message');
        else msg.addClass('message sent-message');

        msg.html('<p class="message-text">' + item['msg'] + '</p>');
        msg.append('<span class="message-time">' + item['time'] + '</span>');
        $('.chat-container').append(msg);
    });

    $('.chat-container').scrollTop($('.chat-container').prop('scrollHeight'));
}

// Загрузка чата с другом
function loadChat(data) {
    // Обработка ошибок сервера
    if (!data) {
        $('.chat-container').html('<div class="chat-info">This chat is not available to you!</div>');
        $('#btnSendMsg').prop('disabled', true);
        return 0;
    }

	let chat = JSON.parse(data.chat);
	let newMsgs = JSON.parse(data.newMsgs);
    
	$('.chat-container').empty();

	// Если оба чата пусты, то выводим сообщение с просьбой ввести сообщение :)
	if (chat.length == 0 && newMsgs.length == 0) {
		$('.chat-container').html('<div class="chat-info">Send a message first!</div>');
		return 0;
	}
	
	chat.length == 0 ? true : getMsgs(chat); // Прорисовка основного чата

	if (newMsgs.length != 0) { 			// Прорисовка чата с новыми сообщениями
		$('.chat-container').append('<div class="newMsgInfo">New message</div>');
		getMsgs(newMsgs);
	}
    
	return 1;
}

// Отображение сообщений (отправленного или из WebSocket)
function getSocketMsg(data) {
    if (data === 0 || data === 1) { 
		alert('Error send message!');
		return 0;
	}
	// Очищаем chat-container, если присутсвует информационное сообщение
	if ($('.chat-info').length) $('.chat-container').empty();

	let msgElement = $('<div></div>');

	if (Number(data.id) === userChatId) msgElement.addClass('message received-message');
	else msgElement.addClass('message sent-message');

    $('#'+ userChatId +' .last-msg').html(data.msg);

	msgElement.html('<p class="message-text">' + data.msg + '</p>');
	msgElement.append('<span class="message-time">' + data.time + '</span>');
	$('.chat-container').append(msgElement);
	$('.chat-container').scrollTop($('.chat-container').prop('scrollHeight'));
}

$(function() {
    // Меняем высоту ввода текста
    $('#text-to-send').on('input', function() {
        autoExpand(this);
    });

    // === Обработка socket сообщений ===
	window.Echo.private('Friendly-chat-' + myProfileId)
    // Новое сообщение
    .listen('.MessageSent', (res) => {
        // Если чат открыт, выводим сообщение, иначе оповещаем о новом сообщении
        if (res.user.id === userChatId) {
            getSocketMsg(res.message); // Выводим сообщение
			
			// Обновляем чаты на сервере
			let url = '/update-chat';

			axios.post(url, { userChatId: userChatId })
			.then(function(response) {})
			.catch(function (error) {
				alert("Error database...\n" + error);
			});
        } else if ($('#option1').is(':checked')) {
            let numNewMsgs = Number($('.newMsg-info-' + res.user.id).html()) + 1;
			$('.newMsg-info-' + res.user.id).css('display', 'block').html(numNewMsgs);
        }
    })
    // Заявка в друзья
    .listen('.NewFriend', (res) => {
        let num = Number($('.numIncom').html());
        num++;
        $('.numIncom').html(num).css('display', 'inline');

        if ($('#option3').is(':checked')) {
            let friend = $('<li></li>').addClass('user_item').attr('id', res.user.id).attr('username', res.user.name).html(res.user.name);
            $('.friends_list').first().append(friend);
        }
    })
    .listen('.DeleteChat', (res) => {
        if (res.user.id === userChatId) {
            $('.chat-container').html('<div class="chat-info">Chat deleted by friend!</div>');
        }
    });

    // Панель для выбора друга
	$('.friends_list').on('click', '.user_item', function() {
		userChatId = Number($(this).attr('id'));
		username = $(this).attr('username');

        // === My friends ===
		if ($('#option1').is(':checked')) { 
            // Загрузка чата с игроками
            let url = '/load-chat';
        
            axios.post(url, {userChatId: userChatId})
                .then(function(res) {
                    loadChat(res.data);
                    $('.newMsg-info-'+ userChatId).css('display', 'none').html(0);
                })
                .catch(function (error) {
                    alert("Error load chat...\n" + error);
                });
        }

        // Отправка сообщения
        $('#btnSendMsg').on('click', function() {
            if ($('#text-to-send').val().trim() !== '' && userChatId !== 0) {
                let sendMsg = $('#text-to-send').val();
                let url = '/sendMsg';

                axios.post(url, { userChatId: userChatId, message: sendMsg })
                    .then(function(res) {
                        getSocketMsg(res.data);
                    })
                    .catch(function (error) {
                        alert("Error send message...\n" + error);
                    });

                $('#text-to-send').val('');
                $('#text-to-send').css('height', '39px');
            }
        });
    });
});