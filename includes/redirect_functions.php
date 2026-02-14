<?php
/**
 * Utility functions for handling redirects and public folder routing
 */

/**
 * Redirect to public folder with proper URL handling
 * @param string $path - Optional path to append
 * @param int $code - HTTP redirect code (301, 302, etc.)
 */
function redirectToPublic($path = '', $code = 301) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Clean up the path
    $path = ltrim($path, '/');
    if (!empty($path)) {
        $path = '/' . $path;
    }
    
    // Build the public URL
    $publicUrl = $protocol . '://' . $host . '/public' . $path;
    
    // Perform the redirect
    header('Location: ' . $publicUrl, true, $code);
    exit;
}

/**
 * Check if current request is in public folder
 * @return bool
 */
function isInPublicFolder() {
    return strpos($_SERVER['REQUEST_URI'], '/public') === 0;
}

/**
 * Get the base public URL
 * @return string
 */
function getPublicBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host . '/public';
}

/**
 * Get current URL with public prefix
 * @return string
 */
function getCurrentPublicUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    if (!isInPublicFolder()) {
        $requestUri = '/public' . $requestUri;
    }
    
    return $protocol . '://' . $host . $requestUri;
}

/**
 * Generate a public URL for internal links
 * @param string $path
 * @return string
 */
function publicUrl($path = '') {
    return getPublicBaseUrl() . '/' . ltrim($path, '/');
}
?>