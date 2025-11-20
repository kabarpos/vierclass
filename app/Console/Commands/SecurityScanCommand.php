<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SecurityScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan 
                            {--type=all : Type of scan (all, files, database, logs, config)}
                            {--report : Generate detailed report}
                            {--fix : Attempt to fix found issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive security scan of the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Starting Security Scan...');
        
        $type = $this->option('type');
        $generateReport = $this->option('report');
        $autoFix = $this->option('fix');
        
        $results = [];
        
        switch ($type) {
            case 'all':
                $results = array_merge(
                    $this->scanFiles(),
                    $this->scanDatabase(),
                    $this->scanLogs(),
                    $this->scanConfiguration(),
                    $this->scanPermissions(),
                    $this->scanDependencies()
                );
                break;
            case 'files':
                $results = $this->scanFiles();
                break;
            case 'database':
                $results = $this->scanDatabase();
                break;
            case 'logs':
                $results = $this->scanLogs();
                break;
            case 'config':
                $results = $this->scanConfiguration();
                break;
            default:
                $this->error('Invalid scan type. Use: all, files, database, logs, config');
                return 1;
        }
        
        $this->displayResults($results);
        
        if ($autoFix) {
            $this->fixIssues($results);
        }
        
        if ($generateReport) {
            $this->generateReport($results);
        }
        
        $this->info('✅ Security scan completed!');
        
        return 0;
    }
    
    /**
     * Scan files for security issues
     */
    private function scanFiles(): array
    {
        $this->info('📁 Scanning files...');
        
        $issues = [];
        
        // Check for sensitive files
        $sensitiveFiles = [
            '.env',
            '.env.example',
            'config/database.php',
            'config/mail.php',
            'config/services.php',
        ];
        
        foreach ($sensitiveFiles as $file) {
            if (File::exists(base_path($file))) {
                $permissions = substr(sprintf('%o', fileperms(base_path($file))), -4);
                if ($permissions !== '0644' && $permissions !== '0600') {
                    $issues[] = [
                        'type' => 'file_permissions',
                        'severity' => 'high',
                        'file' => $file,
                        'current_permissions' => $permissions,
                        'recommended_permissions' => '0600',
                        'description' => 'Sensitive file has incorrect permissions',
                    ];
                }
            }
        }
        
        // Check for backup files
        $backupPatterns = ['*.bak', '*.backup', '*.old', '*.tmp', '*~'];
        foreach ($backupPatterns as $pattern) {
            $files = glob(base_path($pattern));
            foreach ($files as $file) {
                $issues[] = [
                    'type' => 'backup_file',
                    'severity' => 'medium',
                    'file' => $file,
                    'description' => 'Backup file found in application directory',
                ];
            }
        }
        
        // Check for debug files
        $debugFiles = [
            'phpinfo.php',
            'info.php',
            'test.php',
            'debug.php',
        ];
        
        foreach ($debugFiles as $file) {
            if (File::exists(public_path($file))) {
                $issues[] = [
                    'type' => 'debug_file',
                    'severity' => 'high',
                    'file' => $file,
                    'description' => 'Debug file found in public directory',
                ];
            }
        }
        
        // Check for world-writable directories
        $directories = [
            'storage',
            'bootstrap/cache',
        ];
        
        foreach ($directories as $dir) {
            $path = base_path($dir);
            if (File::exists($path)) {
                $permissions = substr(sprintf('%o', fileperms($path)), -4);
                if (substr($permissions, -1) === '7') {
                    $issues[] = [
                        'type' => 'directory_permissions',
                        'severity' => 'medium',
                        'directory' => $dir,
                        'current_permissions' => $permissions,
                        'description' => 'Directory is world-writable',
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Scan database for security issues
     */
    private function scanDatabase(): array
    {
        $this->info('🗄️ Scanning database...');
        
        $issues = [];
        
        try {
            // Check for default passwords
            $users = DB::table('users')
                ->where('password', bcrypt('password'))
                ->orWhere('password', bcrypt('123456'))
                ->orWhere('password', bcrypt('admin'))
                ->get();
            
            foreach ($users as $user) {
                $issues[] = [
                    'type' => 'weak_password',
                    'severity' => 'high',
                    'user_id' => $user->id,
                    'description' => 'User has default or weak password',
                ];
            }
            
            // Check for admin users without proper roles
            $adminUsers = DB::table('users')
                ->where('email', 'like', '%admin%')
                ->orWhere('name', 'like', '%admin%')
                ->get();
            
            foreach ($adminUsers as $user) {
                $hasAdminRole = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_id', $user->id)
                    ->where('roles.name', 'admin')
                    ->exists();
                
                if (!$hasAdminRole) {
                    $issues[] = [
                        'type' => 'admin_without_role',
                        'severity' => 'medium',
                        'user_id' => $user->id,
                        'description' => 'User with admin-like name/email without admin role',
                    ];
                }
            }
            
            // Check for SQL injection vulnerabilities in stored procedures
            $procedures = DB::select("SHOW PROCEDURE STATUS WHERE Db = ?", [config('database.connections.mysql.database')]);
            
            foreach ($procedures as $procedure) {
                $issues[] = [
                    'type' => 'stored_procedure',
                    'severity' => 'low',
                    'procedure' => $procedure->Name,
                    'description' => 'Stored procedure found - review for SQL injection vulnerabilities',
                ];
            }
            
        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'database_error',
                'severity' => 'high',
                'error' => $e->getMessage(),
                'description' => 'Error occurred during database security scan',
            ];
        }
        
        return $issues;
    }
    
    /**
     * Scan logs for security issues
     */
    private function scanLogs(): array
    {
        $this->info('📋 Scanning logs...');
        
        $issues = [];
        
        // Check for recent security threats
        $securityLogPath = storage_path('logs/security.log');
        if (File::exists($securityLogPath)) {
            $logContent = File::get($securityLogPath);
            
            // Count different types of threats in the last 24 hours
            $threatTypes = [
                'sql_injection' => 0,
                'xss_attempt' => 0,
                'brute_force' => 0,
                'suspicious_user_agent' => 0,
            ];
            
            $lines = explode("\n", $logContent);
            $yesterday = now()->subDay();
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                try {
                    $logData = json_decode(substr($line, strpos($line, '{')), true);
                    if ($logData && isset($logData['timestamp'])) {
                        $logTime = \Carbon\Carbon::parse($logData['timestamp']);
                        if ($logTime->greaterThan($yesterday)) {
                            foreach ($threatTypes as $type => $count) {
                                if (str_contains($line, $type)) {
                                    $threatTypes[$type]++;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Skip malformed log entries
                }
            }
            
            foreach ($threatTypes as $type => $count) {
                if ($count > 10) {
                    $issues[] = [
                        'type' => 'high_threat_activity',
                        'severity' => 'high',
                        'threat_type' => $type,
                        'count' => $count,
                        'description' => "High number of {$type} attempts in the last 24 hours",
                    ];
                } elseif ($count > 5) {
                    $issues[] = [
                        'type' => 'moderate_threat_activity',
                        'severity' => 'medium',
                        'threat_type' => $type,
                        'count' => $count,
                        'description' => "Moderate number of {$type} attempts in the last 24 hours",
                    ];
                }
            }
        }
        
        // Check log file permissions
        $logFiles = [
            'laravel.log',
            'security.log',
            'performance.log',
            'monitoring.log',
        ];
        
        foreach ($logFiles as $logFile) {
            $path = storage_path("logs/{$logFile}");
            if (File::exists($path)) {
                $permissions = substr(sprintf('%o', fileperms($path)), -4);
                if ($permissions !== '0644' && $permissions !== '0600') {
                    $issues[] = [
                        'type' => 'log_permissions',
                        'severity' => 'medium',
                        'file' => $logFile,
                        'current_permissions' => $permissions,
                        'description' => 'Log file has incorrect permissions',
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Scan configuration for security issues
     */
    private function scanConfiguration(): array
    {
        $this->info('⚙️ Scanning configuration...');
        
        $issues = [];
        
        // Check debug mode
        if (config('app.debug') === true) {
            $issues[] = [
                'type' => 'debug_mode',
                'severity' => 'high',
                'description' => 'Application is running in debug mode',
            ];
        }
        
        // Check APP_KEY
        if (empty(config('app.key'))) {
            $issues[] = [
                'type' => 'missing_app_key',
                'severity' => 'critical',
                'description' => 'Application key is not set',
            ];
        }
        
        // Check HTTPS enforcement
        if (config('app.env') === 'production' && !config('app.force_https', false)) {
            $issues[] = [
                'type' => 'https_not_enforced',
                'severity' => 'high',
                'description' => 'HTTPS is not enforced in production',
            ];
        }
        
        // Check session configuration
        if (config('session.secure') !== true && config('app.env') === 'production') {
            $issues[] = [
                'type' => 'insecure_session',
                'severity' => 'high',
                'description' => 'Session cookies are not marked as secure',
            ];
        }
        
        if (config('session.http_only') !== true) {
            $issues[] = [
                'type' => 'session_not_http_only',
                'severity' => 'medium',
                'description' => 'Session cookies are not HTTP only',
            ];
        }
        
        // Check CORS configuration
        $corsConfig = config('cors');
        if (isset($corsConfig['allowed_origins']) && in_array('*', $corsConfig['allowed_origins'])) {
            $issues[] = [
                'type' => 'permissive_cors',
                'severity' => 'medium',
                'description' => 'CORS allows all origins',
            ];
        }
        
        // Check database configuration
        if (config('database.default') === 'sqlite' && config('app.env') === 'production') {
            $issues[] = [
                'type' => 'sqlite_in_production',
                'severity' => 'medium',
                'description' => 'SQLite is used in production environment',
            ];
        }
        
        return $issues;
    }
    
    /**
     * Scan file permissions
     */
    private function scanPermissions(): array
    {
        $this->info('🔐 Scanning permissions...');
        
        $issues = [];
        
        // Check critical file permissions
        $criticalFiles = [
            'artisan' => '0755',
            '.env' => '0600',
            'composer.json' => '0644',
            'composer.lock' => '0644',
        ];
        
        foreach ($criticalFiles as $file => $expectedPerm) {
            $path = base_path($file);
            if (File::exists($path)) {
                $actualPerm = substr(sprintf('%o', fileperms($path)), -4);
                if ($actualPerm !== $expectedPerm) {
                    $issues[] = [
                        'type' => 'incorrect_file_permissions',
                        'severity' => 'medium',
                        'file' => $file,
                        'expected' => $expectedPerm,
                        'actual' => $actualPerm,
                        'description' => "File {$file} has incorrect permissions",
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Scan dependencies for vulnerabilities
     */
    private function scanDependencies(): array
    {
        $this->info('📦 Scanning dependencies...');
        
        $issues = [];
        
        // Check for outdated packages (simplified check)
        $composerLock = base_path('composer.lock');
        if (File::exists($composerLock)) {
            $lockData = json_decode(File::get($composerLock), true);
            
            if (isset($lockData['packages'])) {
                foreach ($lockData['packages'] as $package) {
                    // Check for known vulnerable packages (simplified)
                    $vulnerablePackages = [
                        'monolog/monolog' => ['< 1.25.2', '< 2.1.1'],
                        'symfony/http-foundation' => ['< 4.4.7', '< 5.0.7'],
                        'laravel/framework' => ['< 8.83.8'],
                    ];
                    
                    $packageName = $package['name'];
                    if (isset($vulnerablePackages[$packageName])) {
                        $version = $package['version'];
                        foreach ($vulnerablePackages[$packageName] as $vulnerableVersion) {
                            if (version_compare($version, str_replace('< ', '', $vulnerableVersion), '<')) {
                                $issues[] = [
                                    'type' => 'vulnerable_dependency',
                                    'severity' => 'high',
                                    'package' => $packageName,
                                    'version' => $version,
                                    'vulnerable_version' => $vulnerableVersion,
                                    'description' => "Package {$packageName} has known vulnerabilities",
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Display scan results
     */
    private function displayResults(array $results): void
    {
        if (empty($results)) {
            $this->info('✅ No security issues found!');
            return;
        }
        
        $this->warn("⚠️ Found " . count($results) . " security issues:");
        
        $severityColors = [
            'critical' => 'error',
            'high' => 'error',
            'medium' => 'warn',
            'low' => 'info',
        ];
        
        $severityCounts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];
        
        foreach ($results as $issue) {
            $severity = $issue['severity'];
            $severityCounts[$severity]++;
            
            $color = $severityColors[$severity] ?? 'info';
            $this->line('');
            $this->{$color}("🔴 [{$severity}] {$issue['type']}");
            $this->line("   Description: {$issue['description']}");
            
            if (isset($issue['file'])) {
                $this->line("   File: {$issue['file']}");
            }
            
            if (isset($issue['current_permissions'])) {
                $this->line("   Current Permissions: {$issue['current_permissions']}");
            }
            
            if (isset($issue['recommended_permissions'])) {
                $this->line("   Recommended Permissions: {$issue['recommended_permissions']}");
            }
        }
        
        $this->line('');
        $this->info('📊 Summary:');
        foreach ($severityCounts as $severity => $count) {
            if ($count > 0) {
                $this->line("   {$severity}: {$count}");
            }
        }
    }
    
    /**
     * Attempt to fix issues automatically
     */
    private function fixIssues(array $results): void
    {
        $this->info('🔧 Attempting to fix issues...');
        
        $fixed = 0;
        
        foreach ($results as $issue) {
            switch ($issue['type']) {
                case 'file_permissions':
                case 'incorrect_file_permissions':
                    if (isset($issue['file']) && isset($issue['recommended_permissions'])) {
                        $path = base_path($issue['file']);
                        if (File::exists($path)) {
                            chmod($path, octdec($issue['recommended_permissions']));
                            $this->info("✅ Fixed permissions for {$issue['file']}");
                            $fixed++;
                        }
                    }
                    break;
                    
                case 'backup_file':
                case 'debug_file':
                    if (isset($issue['file'])) {
                        $path = $issue['type'] === 'debug_file' 
                            ? public_path($issue['file'])
                            : $issue['file'];
                        
                        if (File::exists($path)) {
                            File::delete($path);
                            $this->info("✅ Removed {$issue['file']}");
                            $fixed++;
                        }
                    }
                    break;
            }
        }
        
        $this->info("🎉 Fixed {$fixed} issues automatically.");
        
        if ($fixed < count($results)) {
            $this->warn("⚠️ " . (count($results) - $fixed) . " issues require manual attention.");
        }
    }
    
    /**
     * Generate detailed security report
     */
    private function generateReport(array $results): void
    {
        $this->info('📄 Generating security report...');
        
        $report = [
            'scan_date' => now()->toISOString(),
            'total_issues' => count($results),
            'issues_by_severity' => [
                'critical' => 0,
                'high' => 0,
                'medium' => 0,
                'low' => 0,
            ],
            'issues_by_type' => [],
            'detailed_results' => $results,
        ];
        
        foreach ($results as $issue) {
            $report['issues_by_severity'][$issue['severity']]++;
            
            if (!isset($report['issues_by_type'][$issue['type']])) {
                $report['issues_by_type'][$issue['type']] = 0;
            }
            $report['issues_by_type'][$issue['type']]++;
        }
        
        $reportPath = storage_path('logs/security_scan_' . now()->format('Y-m-d_H-i-s') . '.json');
        File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->info("📄 Report saved to: {$reportPath}");
        
        // Log the scan results
        Log::channel('security')->info('Security scan completed', [
            'total_issues' => count($results),
            'issues_by_severity' => $report['issues_by_severity'],
            'report_path' => $reportPath,
        ]);
    }
}