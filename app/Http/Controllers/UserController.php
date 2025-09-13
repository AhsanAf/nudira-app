<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string)$request->query('q', ''));
        $role = trim((string)$request->query('role', '')); // '' | 'admin' | 'staff'

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role !== '', fn($query) => $query->where('role', $role))
            ->select('id','name','email','role','created_at','updated_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'      => User::count(),
            'admin'      => User::role('admin')->count(),
            'staff'      => User::role('staff')->count(),
            'lastUpdate' => optional(User::latest('updated_at')->first())->updated_at,
        ];

        return view('users.index', compact('users','q','role','stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:6'],
            'role'     => ['required', Rule::in(['admin','staff'])],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index', [
                'q'    => $request->input('q'),
                'role' => $request->input('role'),
            ])
            ->with('success', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['admin','staff'])],
            'password' => ['nullable','string','min:6'],
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index', [
                'q'    => $request->input('keep_q'),
                'role' => $request->input('keep_role'),
            ])
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Tidak bisa menghapus akun yang sedang login.');
        }

        $user->syncRoles([]);
        $user->delete();

        return redirect()
            ->route('admin.users.index', [
                'q'    => $request->input('keep_q'),
                'role' => $request->input('keep_role'),
            ])
            ->with('success', 'User dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'new_password' => ['required','string','min:6'],
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return back()->with('success', 'Password user telah direset.');
    }
}
