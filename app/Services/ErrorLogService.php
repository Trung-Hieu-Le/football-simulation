<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;

class ErrorLogService
{
    /**
     * Log directory path
     */
    private const LOG_DIR = 'logs/errors';

    /**
     * Log an error to a separate error log file
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param string|null $category Optional category for organizing logs
     * @return void
     */
    public static function logError(string $message, array $context = [], ?string $category = null): void
    {
        try {
            $logDir = storage_path(self::LOG_DIR);
            
            // Create directory if it doesn't exist
            if (!File::exists($logDir)) {
                File::makeDirectory($logDir, 0755, true);
            }

            // Determine log file name
            $fileName = $category ? "error-{$category}.log" : 'error.log';
            $logFile = $logDir . '/' . $fileName;

            // Format log entry
            $timestamp = now()->format('Y-m-d H:i:s');
            $contextString = !empty($context) ? json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
            
            $logEntry = "[{$timestamp}] {$message}";
            if ($contextString) {
                $logEntry .= "\nContext: {$contextString}";
            }
            $logEntry .= "\n" . str_repeat('-', 80) . "\n";

            // Write to file
            File::append($logFile, $logEntry);

            // Also log to Laravel's default log
            Log::error($message, $context);
        } catch (Exception $e) {
            // Fallback to Laravel's default logging if custom logging fails
            Log::error('Failed to write to custom error log', [
                'original_message' => $message,
                'original_context' => $context,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log an exception
     *
     * @param Exception $exception
     * @param array $context Additional context data
     * @param string|null $category Optional category for organizing logs
     * @return void
     */
    public static function logException(Exception $exception, array $context = [], ?string $category = null): void
    {
        $message = "Exception: {$exception->getMessage()}";
        $context['exception'] = [
            'class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        self::logError($message, $context, $category);
    }

    /**
     * Log a database error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function logDatabaseError(string $message, array $context = []): void
    {
        self::logError($message, $context, 'database');
    }

    /**
     * Log a validation error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function logValidationError(string $message, array $context = []): void
    {
        self::logError($message, $context, 'validation');
    }

    /**
     * Log a simulation error
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function logSimulationError(string $message, array $context = []): void
    {
        self::logError($message, $context, 'simulation');
    }

    /**
     * Get all error log files
     *
     * @return array
     */
    public static function getLogFiles(): array
    {
        $logDir = storage_path(self::LOG_DIR);
        
        if (!File::exists($logDir)) {
            return [];
        }

        return File::files($logDir);
    }

    /**
     * Clear error logs
     *
     * @param string|null $category If provided, only clear logs for this category
     * @return bool
     */
    public static function clearLogs(?string $category = null): bool
    {
        try {
            $logDir = storage_path(self::LOG_DIR);
            
            if (!File::exists($logDir)) {
                return true;
            }

            if ($category) {
                $logFile = $logDir . "/error-{$category}.log";
                if (File::exists($logFile)) {
                    File::delete($logFile);
                }
            } else {
                File::cleanDirectory($logDir);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Failed to clear error logs', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

