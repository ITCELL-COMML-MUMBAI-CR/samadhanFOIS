<?php
/**
 * SystemSettings Model
 * Handles system configuration settings
 */

require_once 'BaseModel.php';

class SystemSettings extends BaseModel {
    protected $table = 'system_settings';
    
    /**
     * Get setting value by key
     */
    public function getSetting($key) {
        $stmt = $this->connection->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Set setting value
     */
    public function setSetting($key, $value, $description = null) {
        $stmt = $this->connection->prepare("
            INSERT INTO system_settings (setting_key, setting_value, description, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value), 
            description = VALUES(description), 
            updated_at = VALUES(updated_at)
        ");
        
        $now = getCurrentDateTime();
        return $stmt->execute([$key, $value, $description, $now, $now]);
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings() {
        $stmt = $this->connection->prepare("SELECT * FROM system_settings ORDER BY setting_key ASC");
        $stmt->execute();
        $settings = $stmt->fetchAll();
        
        // Convert to key-value array
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    /**
     * Get settings as associative array
     */
    public function getSettingsArray() {
        $stmt = $this->connection->prepare("SELECT setting_key, setting_value FROM system_settings ORDER BY setting_key ASC");
        $stmt->execute();
        $settings = $stmt->fetchAll();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }
    
    /**
     * Get settings with descriptions
     */
    public function getSettingsWithDescriptions() {
        $stmt = $this->connection->prepare("SELECT * FROM system_settings ORDER BY setting_key ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Delete setting
     */
    public function deleteSetting($key) {
        $stmt = $this->connection->prepare("DELETE FROM system_settings WHERE setting_key = ?");
        return $stmt->execute([$key]);
    }
    
    /**
     * Check if setting exists
     */
    public function settingExists($key) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Get setting statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total settings
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM system_settings");
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['count'];
        
        // Settings with descriptions
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM system_settings 
            WHERE description IS NOT NULL AND description != ''
        ");
        $stmt->execute();
        $stats['with_descriptions'] = $stmt->fetch()['count'];
        
        // Recent settings (last 30 days)
        $stmt = $this->connection->prepare("
            SELECT COUNT(*) as count 
            FROM system_settings 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $stats['recent_30_days'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaultSettings() {
        $defaultSettings = [
            'app_name' => 'SAMPARK FOIS',
            'app_version' => '2.0',
            'max_file_size' => '5242880',
            'allowed_file_types' => 'jpg,jpeg,png,gif',
            'session_timeout' => '1800',
            'email_notifications' => '1',
            'grievance_id_prefix' => 'CMP',
            'transaction_id_prefix' => 'TXN',
            'default_department' => 'COMMERCIAL',
            'auto_assignment' => '1'
        ];
        
        foreach ($defaultSettings as $key => $value) {
            if (!$this->settingExists($key)) {
                $this->setSetting($key, $value, "Default setting for $key");
            }
        }
    }
    
    /**
     * Get application configuration
     */
    public function getAppConfig() {
        return [
            'app_name' => $this->getSetting('app_name') ?: 'SAMPARK FOIS',
            'app_version' => $this->getSetting('app_version') ?: '2.0',
            'max_file_size' => (int)($this->getSetting('max_file_size') ?: 5242880),
            'allowed_file_types' => explode(',', $this->getSetting('allowed_file_types') ?: 'jpg,jpeg,png,gif'),
            'session_timeout' => (int)($this->getSetting('session_timeout') ?: 1800),
            'email_notifications' => (bool)($this->getSetting('email_notifications') ?: 1),
            'grievance_id_prefix' => $this->getSetting('grievance_id_prefix') ?: 'CMP',
            'transaction_id_prefix' => $this->getSetting('transaction_id_prefix') ?: 'TXN',
            'default_department' => $this->getSetting('default_department') ?: 'COMMERCIAL',
            'auto_assignment' => (bool)($this->getSetting('auto_assignment') ?: 1)
        ];
    }
}
