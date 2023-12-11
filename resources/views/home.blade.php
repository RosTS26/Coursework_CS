@extends('layouts.app')

@section('content')
<div class="blockPanelFriend">
    <div class="container">
        <div class="friends-menu">
            <p class="header_P">Friends</p>
            <div class="add-friend">
                <p>Add friend:</p>
                <input type="text" id="friend-name" autocomplete="off" placeholder="enter friend's name">
                <input type="button" class="btns" id="friend-add" value="Add">
            </div>
            <div class="btns-menu">
                <input type="radio" name="option" id="option1" checked="true">
                <label for="option1" class="btns" id="my-friends">My friends</label>
                <input type="radio" name="option" id="option2">
                <label for="option2" class="btns" id="sent-app">Sent app</label>
                <input type="radio" name="option" id="option3">
                <label for="option3" class="btns" id="incoming-app">
                    Incoming app
                    @if ($numIncomApp > 0)
                    <span class="numIncom position-absolute translate-middle badge rounded-pill bg-danger">{{ $numIncomApp }}</span>
                    @elseif ($numIncomApp > 99)
                    <span class="numIncom position-absolute translate-middle badge rounded-pill bg-danger">99+</span>
                    @else
                    <span class="numIncom position-absolute translate-middle badge rounded-pill bg-danger" style="display: none">0</span>
                    @endif
                </label>
            </div>
        </div>
        <div class="friend-profile">
            <p class="header_P">Friend profile</p>
            <div class="fp-row">
                <div class="information">
                    <div id="username">UserName: </div>
                    <div id="ID">ID: </div>
                </div>
                <div class="functions-btns"></div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="friends_info">
            <ul class="friends_list"></ul>
        </div>
        <div class="field_for_message">
            <!-- Chat conteiner -->
            <div class="chat-container">
                <div class="chat-info">Select, who you would like to write to!</div>
            </div>
            <div class="text-and-btn">
                <textarea id="text-to-send" placeholder="Send message..."></textarea>
                <button class="btn" value="Send" id="btnSendMsg" disabled>
                    Send
                </button>
            </div>
        </div>
    </div>
    <div class="res" id="msgInfo"></div>
</div>
<div id="test"></div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@push('scripts')
<script>
    var csrf = "{{ csrf_token() }}";
    var myId = "{{ auth()->user()->id }}";
    var userChatId = 0;
    var username = '';
</script>
<script src="/js/friends.js" defer></script>
<script src="/js/chat.js" defer></script>
@endpush
