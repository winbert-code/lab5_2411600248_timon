<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = User::orderBy('name')->orderBy('id')->paginate(12);

        return view('staff.index', ['staff' => $staff]);
    }

    public function create(): View
    {
        return view('staff.create', ['staffMember' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('staff.index')
            ->with('success', "{$data['name']} was added as a staff user.");
    }

    public function edit(User $staff): View
    {
        return view('staff.edit', ['staffMember' => $staff]);
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$staff->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $staff->name = $data['name'];
        $staff->email = $data['email'];

        if (! empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }

        $staff->save();

        return redirect()->route('staff.index')
            ->with('success', "{$staff->name} was updated.");
    }

    public function destroy(User $staff): RedirectResponse
    {
        if ($staff->id === auth()->id()) {
            return redirect()->route('staff.index')
                ->with('error', 'You cannot remove your own account.');
        }

        $name = $staff->name;
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', "{$name} was removed from staff.");
    }
}
