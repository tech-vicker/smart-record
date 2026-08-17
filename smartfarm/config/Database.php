<?php
/**
 * SmartFarm Database Connection Manager
 * Handles database initialization and error management
 */

class Database {
    private static $instance = null;
    private $db;
    private $lastError = null;

    private function __construct() {
        $this->connect();
    }

    /**
     * Get database singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->db;
    }

    /**
     * Initialize database connection
     */
    private function connect() {
        try {
            $dbPath = Config::get('DB_PATH');
            
            // Create directory if it doesn't exist
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            // Create database file if it doesn't exist
            if (!file_exists($dbPath)) {
                touch($dbPath);
                chmod($dbPath, 0644);
            }
            
            $this->db = new SQLite3($dbPath);
            
            // Enable performance optimizations
            $this->db->exec('PRAGMA journal_mode = WAL');
            $this->db->exec('PRAGMA synchronous = NORMAL');
            $this->db->exec('PRAGMA cache_size = 10000');
            $this->db->exec('PRAGMA temp_store = MEMORY');
            
            // Enable foreign keys
            $this->db->exec('PRAGMA foreign_keys = ON');
            
            $logger = new Logger();
            $logger->info('Database connected successfully', ['path' => $dbPath]);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->critical('Database connection failed', ['error' => $e->getMessage()]);
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get last error
     */
    public static function getLastError() {
        $instance = new self();
        return $instance->lastError ?? $instance->db->lastErrorMsg();
    }

    /**
     * Close database connection
     */
    public static function close() {
        if (self::$instance && self::$instance->db) {
            self::$instance->db->close();
            self::$instance = null;
        }
    }
}

// Initialize database on load
$db = Database::getInstance();
