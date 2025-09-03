<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PermissionModel;

class MasterProductController extends BaseController
{
    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            return $this->doLogin();
        }
        return view('auth/login');
    }

    public function doLogin()
    {
        $session = session();
        $userModel = new UserModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $userModel->where('email', $email)->first();
        
        if ($user && password_verify($password, $user['password'])) {

            $permissionModel = new PermissionModel();
            $permissions = $permissionModel->getPermissionsByRole($user['role_id']);

            $session->set([
                'user_id'     => $user['user_id'],
                'user_email'  => $user['email'],
                'role_id'     => $user['role_id'],
                'permissions' => $permissions,
                'isLoggedIn'  => true,
            ]);

            return redirect()->to('/');
        } else {

            return redirect()->back()->with('error', 'Invalid login details.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
