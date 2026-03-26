<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 'membership' ロールが存在するか確認、存在しない場合は作成
        $membershipRole = Role::firstOrCreate(['name' => 'membership']);

        // 'deleteWord' 権限が存在するか確認、存在しない場合は作成
        $deletePermission = Permission::firstOrCreate(['name' => 'deleteWord']);

        // 'membership' ロールに 'read' 権限を付与
        if (!$membershipRole->hasPermissionTo($deletePermission)) {
            $membershipRole->givePermissionTo($deletePermission);
        }

        // ユーザーに 'membership' ロールを割り当て
        $user->assignRole($membershipRole);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('words.index', absolute: false));
    }
}
