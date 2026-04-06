<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('users/UserList', [
            'title' => 'Registered users',
            'users' => UserResource::collection($users)->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/Create');
    }
}
