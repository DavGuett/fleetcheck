<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreUserRequest $request, CreateNewUser $creator): RedirectResponse
    {
        $creator->create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }
}
