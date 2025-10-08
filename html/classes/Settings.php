<?php

class Settings {
    private $db;
    private static $cache = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get a setting value by key
     */
    public function get($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $stmt = $this->db->prepare("SELECT setting_value, setting_type FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $value = $this->castValue($result['setting_value'], $result['setting_type']);
                self::$cache[$key] = $value;
                return $value;
            }
        } catch (PDOException $e) {
            error_log("Error fetching setting '$key': " . $e->getMessage());
        }

        return $default;
    }

    /**
     * Set a setting value
     */
    public function set($key, $value, $type = 'text') {
        try {
            // Check if setting exists
            $stmt = $this->db->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $exists = $stmt->fetch();

            if ($type === 'encrypted' && $value !== '') {
                $value = $this->encrypt($value);
            }

            if ($exists) {
                // Update existing setting
                $stmt = $this->db->prepare("UPDATE settings SET setting_value = ?, setting_type = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $type, $key]);
            } else {
                // Insert new setting
                $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([$key, $value, $type]);
            }

            // Update cache
            self::$cache[$key] = $this->castValue($value, $type);
            return true;
        } catch (PDOException $e) {
            error_log("Error setting '$key': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all settings
     */
    public function getAll($category = null) {
        try {
            if ($category) {
                $stmt = $this->db->prepare("SELECT * FROM settings WHERE category = ? ORDER BY setting_key");
                $stmt->execute([$category]);
            } else {
                $stmt = $this->db->query("SELECT * FROM settings ORDER BY category, setting_key");
            }

            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = [
                    'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                    'type' => $row['setting_type'],
                    'description' => $row['description'],
                    'category' => $row['category']
                ];
            }

            return $settings;
        } catch (PDOException $e) {
            error_log("Error fetching all settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete a setting
     */
    public function delete($key) {
        try {
            $stmt = $this->db->prepare("DELETE FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);

            // Remove from cache
            unset(self::$cache[$key]);
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting setting '$key': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear settings cache
     */
    public function clearCache() {
        self::$cache = [];
    }

    /**
     * Cast value to appropriate type
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'number':
                return intval($value);
            case 'boolean':
                return $value == '1' || $value === true;
            case 'json':
                return json_decode($value, true);
            case 'encrypted':
                return $this->decrypt($value);
            default:
                return $value;
        }
    }

    /**
     * Simple encryption for sensitive settings
     */
    private function encrypt($value) {
        // In production, use a proper encryption library
        $key = 'YourSecretKey123'; // Should be in environment variable
        return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16)));
    }

    /**
     * Simple decryption for sensitive settings
     */
    private function decrypt($value) {
        if (empty($value)) return '';
        // In production, use a proper encryption library
        $key = 'YourSecretKey123'; // Should be in environment variable
        return openssl_decrypt(base64_decode($value), 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }

    /**
     * Check if maintenance mode is enabled
     */
    public function isMaintenanceMode() {
        return $this->get('maintenance_mode', false);
    }

    /**
     * Get maintenance message
     */
    public function getMaintenanceMessage() {
        return $this->get('maintenance_message', 'We are currently performing maintenance. Please check back later.');
    }

    /**
     * Check if registration is allowed
     */
    public function isRegistrationAllowed() {
        return $this->get('allow_registration', true);
    }

    /**
     * Get session timeout in seconds
     */
    public function getSessionTimeout() {
        return $this->get('session_timeout', 1800);
    }

    /**
     * Get API rate limit
     */
    public function getApiRateLimit() {
        return $this->get('api_rate_limit', 100);
    }

    /**
     * Export settings to array (for backup)
     */
    public function export($includeEncrypted = false) {
        try {
            $stmt = $this->db->query("SELECT * FROM settings ORDER BY setting_key");
            $settings = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (!$includeEncrypted && $row['setting_type'] === 'encrypted') {
                    continue;
                }
                $settings[] = $row;
            }

            return $settings;
        } catch (PDOException $e) {
            error_log("Error exporting settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Import settings from array (for restore)
     */
    public function import($settings) {
        try {
            $this->db->beginTransaction();

            foreach ($settings as $setting) {
                $this->set(
                    $setting['setting_key'],
                    $setting['setting_value'],
                    $setting['setting_type'] ?? 'text'
                );
            }

            $this->db->commit();
            $this->clearCache();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error importing settings: " . $e->getMessage());
            return false;
        }
    }
}