<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressSitemapXml
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // فقط فایل‌های XML بزرگتر از ۱KB را فشرده کن
        if (str_contains($response->headers->get('Content-Type') ?? '', 'application/xml') &&
            strlen($response->getContent()) > 1024) {

            // اگر مرورگر gzip را پشتیبانی می‌کند
            if (str_contains($request->header('Accept-Encoding') ?? '', 'gzip')) {
                $content = gzencode($response->getContent(), 9);

                $response->setContent($content);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', strlen($content));
                $response->headers->set('Vary', 'Accept-Encoding');
            }
        }

        return $response;
    }
}
