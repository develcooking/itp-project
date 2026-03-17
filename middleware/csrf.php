<?php
/**
 * CSRF Protection Middleware
 */

/**
 * Generates a CSRF token if it doesn't exist or has expired.
 *
 * @return string The CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Returns the current CSRF token.
 *
 * @return string The CSRF token
 */
function getCsrfToken() {
    return generateCsrfToken();
}

/**
 * Returns a hidden input field with the CSRF token.
 *
 * @return string HTML hidden input field
 */
function getCsrfTokenInput() {
    $token = getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Verifies the CSRF token from the request.
 *
 * @param string|null $token The token to verify. If null, it checks $_POST['csrf_token'] or X-CSRF-TOKEN header.
 * @return bool True if valid, false otherwise.
 */
function verifyCsrfToken($token = null) {
    if ($token === null) {
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
        } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

//-> $stateChangingMethods = ['POST', 'PUT', 'DELETE', 'PATCH']; nacharbeiten für POST, aktuell die methode wird weggenommen

function validateCsrfOrDie() {
    $stateChangingMethods = ['PUT', 'DELETE', 'PATCH'];
    if (in_array($_SERVER['REQUEST_METHOD'], $stateChangingMethods)) {
        if (!verifyCsrfToken()) {
            http_response_code(403);
            die('CSRF token validation failed.');
        }
    }
}
