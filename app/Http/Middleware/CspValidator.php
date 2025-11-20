<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CspValidator
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only process HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'text/html') === false) {
            return $response;
        }

        // Get generated CSP nonce from previous middleware
        $nonce = $request->attributes->get('csp_nonce');
        if (!$nonce) {
            return $response;
        }

        $content = $response->getContent();
        $originalLength = is_string($content) ? strlen($content) : 0;

        // Lightweight guard: skip extremely large payloads (> 2MB)
        if ($originalLength > 2 * 1024 * 1024) {
            return $response;
        }

        // Inject nonce into inline <script> tags without src/nonce
        $scriptPattern = '/<script(?![^>]*\bsrc=)(?![^>]*\bnonce=)([^>]*)>/i';
        $stylePattern  = '/<style(?![^>]*\bnonce=)([^>]*)>/i';

        $scriptsBefore = preg_match_all($scriptPattern, $content, $m1);
        $stylesBefore  = preg_match_all($stylePattern, $content, $m2);

        if ($scriptsBefore > 0 || $stylesBefore > 0) {
            $content = preg_replace($scriptPattern, '<script$1 nonce="' . $nonce . '">', $content);
            $content = preg_replace($stylePattern, '<style$1 nonce="' . $nonce . '">', $content);

            $response->setContent($content);

            $response->headers->set('X-CSP-Validator', sprintf('nonce-injected; scripts=%d; styles=%d', $scriptsBefore, $stylesBefore));

            if (config('app.debug')) {
                Log::info('CSP Validator injected nonces', [
                    'path' => $request->path(),
                    'scripts_missing_nonce' => $scriptsBefore,
                    'styles_missing_nonce' => $stylesBefore,
                    'length' => $originalLength,
                    'env' => config('app.env'),
                ]);
            }
        } else {
            // Still add a header to indicate validation ran
            $response->headers->set('X-CSP-Validator', 'ok');
        }

        return $response;
    }
}
