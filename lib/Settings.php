<?php

class Settings
{
    private static $cache = [];

    public static function get($key, $default = null)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetchColumn();
            $value = $row !== false ? $row : $default;
            self::$cache[$key] = $value;
            return $value;
        } catch (Exception $e) {
            return $default;
        }
    }

    public static function set($key, $value)
    {
        try {
            $pdo = self::getDB();
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$key, $value]);
            self::$cache[$key] = $value;
            return true;
        } catch (Exception $e) {
            error_log('Settings::set error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getAll()
    {
        try {
            $pdo = self::getDB();
            $rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }

    private static function getDB()
    {
        if (!isset($GLOBALS['pdo'])) {
            require_once __DIR__ . '/../db_connect.php';
        }
        return $GLOBALS['pdo'];
    }
}