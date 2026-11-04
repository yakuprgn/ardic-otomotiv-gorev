<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        return $request->session()->get('personnel_admin') === true
            ? redirect()->route('employees.index')
            : view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:500'],
        ]);
        $username = (string) config('personnel.admin_username');
        $hash = (string) config('personnel.admin_password_hash');

        if ($username === '' || $hash === '' || ! hash_equals($username, $credentials['username']) || ! password_verify($credentials['password'], $hash)) {
            return back()->withInput($request->only('username'))->withErrors(['username' => 'Kullanıcı adı veya parola hatalı.']);
        }

        $request->session()->regenerate();
        $request->session()->put('personnel_admin', true);

        return redirect()->intended(route('employees.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'Güvenli şekilde çıkış yaptınız.');
    }
}
