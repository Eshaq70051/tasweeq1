<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GzipResponse
{
    /**
     * Compress outgoing HTTP responses using gzip if client accepts it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Do not compress binary file responses or streamed responses
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        // Check if gzip is supported by PHP and requested by client
        if (
            function_exists('gzencode') &&
            str_contains($request->header('Accept-Encoding', ''), 'gzip') &&
            !$response->headers->has('Content-Encoding')
        ) {
            $content = $response->getContent();

            // Only compress if content is non-empty and greater than 1KB
            if (is_string($content) && strlen($content) > 1024) {
                $contentType = $response->headers->get('Content-Type', '');

                if (
                    str_contains($contentType, 'application/json') ||
                    str_contains($contentType, 'text/html') ||
                    str_contains($contentType, 'text/plain') ||
                    str_contains($contentType, 'text/css') ||
                    str_contains($contentType, 'application/javascript')
                ) {
                    $compressed = gzencode($content, 6);
                    if ($compressed !== false && strlen($compressed) < strlen($content)) {
                        $response->setContent($compressed);
                        $response->headers->set('Content-Encoding', 'gzip');
                        $response->headers->set('Content-Length', (string) strlen($compressed));
                    }
                }
            }
        }

        return $response;
    }
}
