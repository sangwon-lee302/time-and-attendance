<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of all non-admin users.
     */
    public function index(): View
    {
        $users = User::where('is_admin', false)
            ->select('id', 'name', 'email')
            ->get();

        return view('admin.users-index', ['users' => $users]);
    }
}
