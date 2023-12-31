<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Получаем массив с заявками
        $incomApp = json_decode(auth()->user()->friend->incoming_app);
        $numIncomApp = count($incomApp);

        return view('home', compact('numIncomApp'));
    }
}
