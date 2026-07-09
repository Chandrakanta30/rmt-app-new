<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');
        
        if (!is_logged_in()) {
            return redirect()->to(base_url('login'))->with('error', 'Please login to access this page.');
        }

        if (empty($arguments)) {
            return; // no permission required
        }

        foreach ($arguments as $permission) {
            if (has_permission($permission)) {
                return; // has at least one of the required permissions
            }
        }

        // Access Denied! Render 403 page
        $response = service('response');
        $response->setStatusCode(403);
        
        return $response->setBody(view('errors/html/error_403', [
            'message' => 'You do not have permission to access this resource.'
        ]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
