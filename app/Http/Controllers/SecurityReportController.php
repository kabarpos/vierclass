<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SecurityReportController extends Controller
{
    /**
     * Handle Content Security Policy violation reports
     */
    public function cspReport(Request $request): Response
    {
        try {
            $report = $request->json()->all();
            
            if (isset($report['csp-report'])) {
                $violation = $report['csp-report'];
                
                // Log CSP violation
                Log::channel('security')->warning('CSP Violation Detected', [
                    'document_uri' => $violation['document-uri'] ?? null,
                    'referrer' => $violation['referrer'] ?? null,
                    'violated_directive' => $violation['violated-directive'] ?? null,
                    'effective_directive' => $violation['effective-directive'] ?? null,
                    'original_policy' => $violation['original-policy'] ?? null,
                    'blocked_uri' => $violation['blocked-uri'] ?? null,
                    'line_number' => $violation['line-number'] ?? null,
                    'column_number' => $violation['column-number'] ?? null,
                    'source_file' => $violation['source-file'] ?? null,
                    'status_code' => $violation['status-code'] ?? null,
                    'script_sample' => $violation['script-sample'] ?? null,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'timestamp' => now(),
                ]);
                
                // Track violation statistics
                $this->trackViolationStats('csp', $violation);
                
                // Check if this is a critical violation
                if ($this->isCriticalCspViolation($violation)) {
                    $this->alertCriticalViolation('CSP', $violation, $request);
                }
            }
            
            return response('', 204);
        } catch (\Exception $e) {
            Log::error('Error processing CSP report', [
                'error' => $e->getMessage(),
                'request_body' => $request->getContent(),
            ]);
            
            return response('', 400);
        }
    }
    
    /**
     * Handle HTTP Public Key Pinning violation reports
     */
    public function hpkpReport(Request $request): Response
    {
        try {
            $report = $request->json()->all();
            
            // Log HPKP violation
            Log::channel('security')->critical('HPKP Violation Detected', [
                'hostname' => $report['hostname'] ?? null,
                'port' => $report['port'] ?? null,
                'effective_expiration_date' => $report['effective-expiration-date'] ?? null,
                'include_subdomains' => $report['include-subdomains'] ?? null,
                'noted_hostname' => $report['noted-hostname'] ?? null,
                'served_certificate_chain' => $report['served-certificate-chain'] ?? null,
                'validated_certificate_chain' => $report['validated-certificate-chain'] ?? null,
                'known_pins' => $report['known-pins'] ?? null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            
            // HPKP violations are always critical
            $this->alertCriticalViolation('HPKP', $report, $request);
            
            return response('', 204);
        } catch (\Exception $e) {
            Log::error('Error processing HPKP report', [
                'error' => $e->getMessage(),
                'request_body' => $request->getContent(),
            ]);
            
            return response('', 400);
        }
    }
    
    /**
     * Handle Certificate Transparency violation reports
     */
    public function ctReport(Request $request): Response
    {
        try {
            $report = $request->json()->all();
            
            // Log CT violation
            Log::channel('security')->warning('Certificate Transparency Violation', [
                'hostname' => $report['hostname'] ?? null,
                'port' => $report['port'] ?? null,
                'effective_expiration_date' => $report['effective-expiration-date'] ?? null,
                'served_certificate_chain' => $report['served-certificate-chain'] ?? null,
                'scts' => $report['scts'] ?? null,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
            
            return response('', 204);
        } catch (\Exception $e) {
            Log::error('Error processing CT report', [
                'error' => $e->getMessage(),
                'request_body' => $request->getContent(),
            ]);
            
            return response('', 400);
        }
    }
    
    /**
     * Handle Network Error Logging reports
     */
    public function nelReport(Request $request): Response
    {
        try {
            $reports = $request->json()->all();
            
            foreach ($reports as $report) {
                Log::channel('security')->info('Network Error Logging Report', [
                    'referrer' => $report['referrer'] ?? null,
                    'sampling_fraction' => $report['sampling_fraction'] ?? null,
                    'server_ip' => $report['server_ip'] ?? null,
                    'protocol' => $report['protocol'] ?? null,
                    'method' => $report['method'] ?? null,
                    'status_code' => $report['status_code'] ?? null,
                    'elapsed_time' => $report['elapsed_time'] ?? null,
                    'type' => $report['type'] ?? null,
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'timestamp' => now(),
                ]);
            }
            
            return response('', 204);
        } catch (\Exception $e) {
            Log::error('Error processing NEL report', [
                'error' => $e->getMessage(),
                'request_body' => $request->getContent(),
            ]);
            
            return response('', 400);
        }
    }
    
    /**
     * Get security violation statistics
     */
    public function getViolationStats(Request $request): Response
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:csp,hpkp,ct,nel',
            'period' => 'in:hour,day,week,month',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }
        
        $type = $request->get('type');
        $period = $request->get('period', 'day');
        
        $stats = Cache::get("security_violations_{$type}_{$period}", []);
        
        return response()->json([
            'type' => $type,
            'period' => $period,
            'stats' => $stats,
            'generated_at' => now(),
        ]);
    }
    
    /**
     * Track violation statistics
     */
    private function trackViolationStats(string $type, array $violation): void
    {
        $periods = ['hour', 'day', 'week', 'month'];
        
        foreach ($periods as $period) {
            $key = "security_violations_{$type}_{$period}";
            $stats = Cache::get($key, []);
            
            $timeKey = $this->getTimeKey($period);
            
            if (!isset($stats[$timeKey])) {
                $stats[$timeKey] = [
                    'count' => 0,
                    'violations' => [],
                ];
            }
            
            $stats[$timeKey]['count']++;
            $stats[$timeKey]['violations'][] = [
                'directive' => $violation['violated-directive'] ?? $violation['type'] ?? 'unknown',
                'blocked_uri' => $violation['blocked-uri'] ?? $violation['hostname'] ?? 'unknown',
                'timestamp' => now()->toISOString(),
            ];
            
            // Keep only last 100 violations per time period
            if (count($stats[$timeKey]['violations']) > 100) {
                $stats[$timeKey]['violations'] = array_slice($stats[$timeKey]['violations'], -100);
            }
            
            // Set cache with appropriate TTL
            $ttl = $this->getCacheTtl($period);
            Cache::put($key, $stats, $ttl);
        }
    }
    
    /**
     * Check if CSP violation is critical
     */
    private function isCriticalCspViolation(array $violation): bool
    {
        $criticalDirectives = [
            'script-src',
            'object-src',
            'base-uri',
            'form-action',
        ];
        
        $violatedDirective = $violation['violated-directive'] ?? '';
        
        foreach ($criticalDirectives as $directive) {
            if (str_contains($violatedDirective, $directive)) {
                return true;
            }
        }
        
        // Check for inline script violations
        if (str_contains($violatedDirective, 'script-src') && 
            str_contains($violation['blocked-uri'] ?? '', 'inline')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Send alert for critical violations
     */
    private function alertCriticalViolation(string $type, array $violation, Request $request): void
    {
        $alertData = [
            'type' => $type,
            'violation' => $violation,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'timestamp' => now(),
            'severity' => 'critical',
        ];
        
        // Log critical alert
        Log::channel('security')->critical("Critical {$type} Violation Alert", $alertData);
        
        // Here you could integrate with notification services
        // Example: Slack, email, SMS, etc.
        
        // Increment critical violation counter
        $key = "critical_violations_{$type}_" . now()->format('Y-m-d-H');
        Cache::increment($key, 1);
        Cache::expire($key, 3600); // 1 hour TTL
    }
    
    /**
     * Get time key for statistics
     */
    private function getTimeKey(string $period): string
    {
        return match ($period) {
            'hour' => now()->format('Y-m-d-H'),
            'day' => now()->format('Y-m-d'),
            'week' => now()->format('Y-W'),
            'month' => now()->format('Y-m'),
            default => now()->format('Y-m-d'),
        };
    }
    
    /**
     * Get cache TTL for period
     */
    private function getCacheTtl(string $period): int
    {
        return match ($period) {
            'hour' => 3600, // 1 hour
            'day' => 86400, // 24 hours
            'week' => 604800, // 7 days
            'month' => 2592000, // 30 days
            default => 86400,
        };
    }
}