<?php
/**
 * Input Validation and Sanitization Helper
 */

/**
 * Sanitize string input
 */
function sanitize_string($input) {
    if (empty($input)) return '';
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize integer input
 */
function sanitize_int($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Validate email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate username (alphanumeric, 3-20 chars)
 */
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Validate password strength
 */
function validate_password($password) {
    if (strlen($password) < 6) {
        return false;
    }
    return true;
}

/**
 * Get POST data with sanitization
 */
function get_post($key, $type = 'string') {
    if (!isset($_POST[$key])) {
        return null;
    }

    switch ($type) {
        case 'int':
            return (int)sanitize_int($_POST[$key]);
        case 'string':
        default:
            return sanitize_string($_POST[$key]);
    }
}

/**
 * Get GET data with sanitization
 */
function get_get($key, $type = 'string') {
    if (!isset($_GET[$key])) {
        return null;
    }

    switch ($type) {
        case 'int':
            return (int)sanitize_int($_GET[$key]);
        case 'string':
        default:
            return sanitize_string($_GET[$key]);
    }
}

/**
 * Validate required fields
 */
function validate_required($fields) {
    $missing = [];
    foreach ($fields as $key => $label) {
        if (!isset($_POST[$key]) || empty($_POST[$key])) {
            $missing[] = $label;
        }
    }
    return $missing;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['redirect_message'] = $message;
    $_SESSION['redirect_message_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Display redirect message if exists
 */
function display_redirect_message() {
    if (isset($_SESSION['redirect_message'])) {
        $message = $_SESSION['redirect_message'];
        $type = $_SESSION['redirect_message_type'] ?? 'info';

        $color = 'blue';
        if ($type === 'error') $color = 'red';
        if ($type === 'success') $color = 'green';
        if ($type === 'warning') $color = 'orange';

        echo "<div class='alert' style='background:#f0f4c3;border-left:5px solid #{$color};padding:10px;margin:10px 0;text-align:center;'>{$message}</div>";

        unset($_SESSION['redirect_message']);
        unset($_SESSION['redirect_message_type']);
    }
}
?>
