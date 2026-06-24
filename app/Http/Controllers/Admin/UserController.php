<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of non-admin users.
     */
    public function index(): View
    {
        $users = User::where('is_admin', false)
            ->select('id', 'name', 'email')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }
}
