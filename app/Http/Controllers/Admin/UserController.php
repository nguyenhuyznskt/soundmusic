<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()->when($request->q, fn ($q, $term) => $q->where(fn ($x) => $x->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('username', 'like', "%{$term}%")))
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))->latest()->paginate(20)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id && $request->boolean('is_active') === false, 422, 'Không thể tự khóa tài khoản quản trị đang dùng.');
        $data = $request->validate(['role' => ['required', 'in:listener,artist,admin'], 'is_active' => ['required', 'boolean']]);
        $user->update($data);
        return back()->with('success', 'Đã cập nhật tài khoản.');
    }
}
