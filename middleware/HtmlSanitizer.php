<?php
/**
 * HTML Sanitizer using HTMLPurifier
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

class HtmlSanitizer {
    private static $purifier = null;

    /**
     * Initializes HTMLPurifier with a standard configuration.
     */
    private static function init() {
        if (self::$purifier === null) {
            if (class_exists('HTMLPurifier')) {
                $config = HTMLPurifier_Config::createDefault();
                
                // Example configuration for allowed tags and attributes
                $config->set('HTML.Allowed', 'h1,h2,h3,h4,h5,h6,p,br,strong,em,u,s,blockquote,pre,ol,ul,li,a[href|title]');
                $config->set('HTML.TargetBlank', true);
                $config->set('AutoFormat.RemoveEmpty', true);
                
                self::$purifier = new HTMLPurifier($config);
            }
        }
    }

    /**
     * Sanitizes HTML content.
     * 
     * @param string $html The HTML content to sanitize.
     * @return string The sanitized HTML.
     */
    public static function sanitize($html) {
        self::init();
        
        if (self::$purifier) {
            return self::$purifier->purify($html);
        }
        
        // Fallback if HTMLPurifier is not installed yet
        #return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
        die('Please contact an administrator, a libary is missing.');
    }

    /**
     * Simple wrapper for htmlspecialchars to prevent XSS in plain text outputs.
     * 
     * @param string $text The text to escape.
     * @return string The escaped text.
     */
    public static function escape($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}
