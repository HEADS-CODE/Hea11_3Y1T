<?php

namespace App\Controllers;

class Users extends BaseController
{
    public function index(): string
    {
        $data['users'] = [
            [
                'username'  => 'admin01',
                'full_name' => 'Hannah Elisha A. Delos Santos',
                'role'      => 'Administrator',
            ],
            [
                'username'  => 'manager01',
                'full_name' => 'Jen Raina R. Teodoro',
                'role'      => 'Manager',
            ],
            [
                'username'  => 'cashier01',
                'full_name' => 'Joy Anne Ciaris B. Nuqui',
                'role'      => 'Cashier',
            ],
            [
                'username'  => 'supervisor01',
                'full_name' => 'Cedrick Nicholas C. Valera',
                'role'      => 'Supervisor',
            ],
            [
                'username'  => 'cashier02',
                'full_name' => 'Miles Edgeworth',
                'role'      => 'Cashier',
            ],
            [
                'username'  => 'staff01',
                'full_name' => 'Genya Shinazugawa',
                'role'      => 'Staff',
            ],
        ];

        return view('users', $data);
    }
}