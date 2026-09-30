<?php

namespace App\Controllers;

use App\Models\User;

class Login extends BaseController
{
    protected User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index()
    {
        if (session()->has('user_id')) {
            return redirect()->to(base_url('accounts'));
        }

        return view('login', [
            'title' => 'Login - Puihaha Electric',
            'page' => 'login',
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function authenticate()
    {
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if (! $this->validate([
            'email' => 'required|valid_email',
            'password' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Enter a valid email and password.');
        }

        $user = $this->userModel->findByEmail($email);

        if (! $user || ! $this->userModel->verifyPassword($password, $user['password']) || ! (bool) $user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'The email or password is incorrect.');
        }

        session()->regenerate(true);
        session()->set([
            'user_id' => $user['id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'user_email' => $user['email'],
            'isLogged' => true,
            'is_logged_in' => true,
        ]);

        return redirect()->to(base_url('accounts'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'You have been logged out.');
    }
}
