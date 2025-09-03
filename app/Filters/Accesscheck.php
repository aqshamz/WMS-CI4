<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Accesscheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $permissionName = $arguments[0] ?? null;
        $menuId = $arguments[1] ?? null;
        $subMenuId = $arguments[2] ?? null;

        if (!hasPermission($permissionName, $menuId, $subMenuId, true)) {
            return redirect()->to('/')->with('error', 'Unauthorized access.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action after
    }
}
