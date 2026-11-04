<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        return $request->session()->get('training_admin') === true ? redirect()->route('dashboard') : view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['username' => ['required','string','max:120'], 'password' => ['required','string','max:500']]);
        $username = (string) config('training.admin_username');
        $hash = (string) config('training.admin_password_hash');
        if ($username === '' || $hash === '' || ! hash_equals($username, $data['username']) || ! password_verify($data['password'], $hash)) {
            return back()->withInput($request->only('username'))->withErrors(['username' => 'Kullanıcı adı veya parola hatalı.']);
        }
        $request->session()->regenerate();
        $request->session()->put('training_admin', true);
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('status', 'Güvenli şekilde çıkış yaptınız.');
    }
}
