<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function show(User $user): Response
    {

        Gate::authorize('view', $user);

        return Inertia::render('profile/show', [
            'user_data' => $user,
            'contacts' => $user->contacts,
        ]);
    }

    public function profile(): Response
    {
        $user = Auth::user();

        return Inertia::render('profile/show', [
            'user_data' => $user,
            'contacts' => $user->contacts,
        ]);
    }

    public function teams(): Response
    {
        $user = Auth::user();

        return Inertia::render('teams/index', [
            'teams' => $user->teams()->get(),
        ]);
    }
}
