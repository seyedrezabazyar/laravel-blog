<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Performance
{
public function handle(Request $request, Closure $next)
{
$response = $next($request);

if ($response->headers->has('Content-Type') &&
strpos($response->headers->get('Content-Type'), 'text/html') !== false) {

// Add performance headers
$response->header('Cache-Control', 'public, max-age=60');

// Enable HTTP/2 server push for critical assets
$response->header('Link', '<' . asset('images/default-book.png') . '>; rel=preload; as=image');
}

return $response;
}
}
