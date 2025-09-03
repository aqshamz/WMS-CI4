<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('role_id'); // role_id dari session login

        if (!$userRole) {
            return redirect()->to('/login')->with('error', 'Session expired, please login again.');
        }

        if (!$arguments) {
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }

        if (!in_array($userRole, $arguments)) {
            return redirect()->to('/')->with('error', 'You do not have permission.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
