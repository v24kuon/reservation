<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexUsersRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request): View
    {
        $query = User::query();

        $filters = $request->validated();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }
        // Manage only general users here
        $query->where('role', User::ROLE_USER);
        if (!empty($filters['registered_from'])) {
            $query->whereDate('created_at', '>=', $filters['registered_from']);
        }
        if (!empty($filters['registered_to'])) {
            $query->whereDate('created_at', '<=', $filters['registered_to']);
        }

        $users = $query->orderByDesc('id')->paginate(50)->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        // Force general user role
        $user->role = User::ROLE_USER;
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('admin.users.show', $user)->with('status', 'ユーザーを作成しました');
    }

    public function show(User $user): View
    {
        // Manage only general users; hide others
        abort_unless($user->role === User::ROLE_USER, 404);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === User::ROLE_USER, 404);
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->name = $data['name'];
        $user->email = $data['email'];
        // Do not allow role change here; keep as-is (must be ROLE_USER)
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('admin.users.show', $user)->with('status', 'ユーザーを更新しました');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_USER, 404);
        $this->authorize('delete', $user);
        $user->delete();
        return redirect()->route('admin.users.index')->with('status', 'ユーザーを削除しました');
    }
}
