<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HistoryLogin;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    /**
     * Display a listing of the login history.
     */
    public function index()
    {
        return view('admin.login-history.index');
    }
}
