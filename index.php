<?php
/**
 * Root redirect to public folder
 * This file redirects all requests from the root directory to the public folder
 */

// Function to redirect to public folder
function redirectToPublic() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Check if we're already in public folder
    if (strpos($requestUri, '/public') === 0) {
        return; // Already in public, no redirect needed
    }
    
    // Build the redirect URL
    $publicUrl = $protocol . '://' . $host . '/public' . $requestUri;
    
    // Perform the redirect
    header('Location: ' . $publicUrl, true, 301);
    exit;
}

// Check if the request is for the root and redirect
if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    header('Location: ' . $protocol . '://' . $host . '/public/', true, 301);
    exit;
}

// For other requests, redirect to public folder
redirectToPublic();
?>