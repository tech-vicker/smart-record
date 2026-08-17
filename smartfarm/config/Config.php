<?php
/**
 * SmartFarm Configuration Manager
 * Loads environment variables and provides global configuration
 */

class Config {
    private static $config = [];
    private static $loaded = false;

    /**
     * Load configuration from .env file
     */
    public static function load() {
        if (self::$loaded) return;

        $envPath = __DIR__ . '/../.env';
        
        // Load .env file if it exists
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) continue;
                
                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                        (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                        $value = substr($value, 1, -1);
                    }
                    
                    self::$config[$key] = $value;
                    // Also set as environment variable
                    putenv("$key=$value");
                }
            }
        }
        
        // Override with actual environment variables if set
        $keys = ['APP_ENV', 'APP_DEBUG', 'DB_PATH', 'DB_BACKUP_PATH', 'SESSION_TIMEOUT',
                 'PASSWORD_MIN_LENGTH', 'MAX_LOGIN_ATTEMPTS', 'LOCKOUT_DURATION',
                 'SESSION_SECURE_COOKIE', 'SESSION_HTTPONLY', 'SESSION_SAMESITE',
                 'LOG_LEVEL', 'LOG_PATH'];
        
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                self::$config[$key] = $value;
            }
        }
        
        // Set defaults if not provided
        self::setDefaults();
        self::$loaded = true;
    }

    /**
     * Set default configuration values
     */
    private static function setDefaults() {
        $defaults = [
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'DB_PATH' => __DIR__ . '/../db/database.sqlite',
            'DB_BACKUP_PATH' => __DIR__ . '/../db/backups',
            'SESSION_TIMEOUT' => 3600,
            'PASSWORD_MIN_LENGTH' => 8,
            'MAX_LOGIN_ATTEMPTS' => 5,
            'LOCKOUT_DURATION' => 900,
            'SESSION_SECURE_COOKIE' => 'false',
            'SESSION_HTTPONLY' => 'true',
            'SESSION_SAMESITE' => 'Lax',
            'LOG_LEVEL' => 'info',
            'LOG_PATH' => __DIR__ . '/../logs'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!isset(self::$config[$key])) {
                self::$config[$key] = $value;
            }
        }
    }

    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) self::load();
        return self::$config[$key] ?? $default;
    }

    /**
     * Get all configuration
     */
    public static function all() {
        if (!self::$loaded) self::load();
        return self::$config;
    }

    /**
     * Check if in debug mode
     */
    public static function isDebug() {
        $debug = self::get('APP_DEBUG', 'false');
        return $debug === 'true' || $debug === true || $debug === '1';
    }

    /**
     * Check if in production
     */
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }
}

// Auto-load configuration
Config::load();
