<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index() : Response
    {
        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get();

        return Inertia::render('users/UserList', [
            'title' => 'Registered users',
            'users' => $users,
        ]);
    }
}
