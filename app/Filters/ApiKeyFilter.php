<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;


class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $expected = (string) env('api.key', '');

        $provided = (string) $request->getHeaderLine('X-API-KEY');
        if ($provided === '') {
            $auth = (string) $request->getHeaderLine('Authorization');
            if (stripos($auth, 'Bearer ') === 0) {
                $provided = trim(substr($auth, 7));
            }
        }

        if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => false,
                    'message' => $expected === ''
                        ? 'API is not configured: set api.key in .env'
                        : 'Unauthorized: invalid or missing API key',
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do
    }
}
