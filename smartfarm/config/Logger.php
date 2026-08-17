<?php
/**
 * SmartFarm Logger
 * Handles application logging to file
 */

class Logger {
    private $logPath;
    private $logLevel;
    private $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];

    public function __construct() {
        $this->logPath = Config::get('LOG_PATH', __DIR__ . '/../logs');
        $this->logLevel = Config::get('LOG_LEVEL', 'info');
        
        // Create logs directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log a message
     */
    public function log($level, $message, $context = []) {
        // Check if message should be logged based on level
        if ($this->levels[$level] < $this->levels[$this->logLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $logFile = $this->logPath . '/' . strtolower($level) . '-' . date('Y-m-d') . '.log';
        
        // Format message with context
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        // Append to log file
        error_log($logMessage, 3, $logFile);
    }

    public function debug($message, $context = []) { $this->log('DEBUG', $message, $context); }
    public function info($message, $context = []) { $this->log('INFO', $message, $context); }
    public function warning($message, $context = []) { $this->log('WARNING', $message, $context); }
    public function error($message, $context = []) { $this->log('ERROR', $message, $context); }
    public function critical($message, $context = []) { $this->log('CRITICAL', $message, $context); }
}

$logger = new Logger();
