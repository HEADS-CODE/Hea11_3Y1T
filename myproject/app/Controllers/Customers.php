<?php

namespace App\Controllers;

class Customers extends BaseController
{
    public function index(): string
    {
        $data['customers'] = [
            [
                'full_name' => 'Hannah Elisha A. Delos Santos',
                'email'     => 'hadelossantos@fit.edu.ph',
                'phone'     => '09913456789',
            ],
            [
                'full_name' => 'Jen Raina R. Teodoro',
                'email'     => 'jrteodoro@fit.edu.ph',
                'phone'     => '091934567890',
            ],
            [
                'full_name' => 'Joy Anne Ciaris B. Nuqui',
                'email'     => 'jbnuqui@fit.edu.ph',
                'phone'     => '099145678901',
            ],
            [
                'full_name' => 'Cedrick Nicholas C. Valera',
                'email'     => 'ccvalera@fit.edu.ph',
                'phone'     => '09456789012',
            ],
            [
                'full_name' => 'Miles Edgeworth',
                'email'     => 'milesEdgeworth@example.com',
                'phone'     => '09196748938',
                
            ],
            [
                'full_name' => 'Genya Shinazugawa',
                'email'     => 'gShinazugawa123@example.com',
                'phone'     => '09289403943',
                
            ],
        ];

        return view('customers', $data);
    }
}