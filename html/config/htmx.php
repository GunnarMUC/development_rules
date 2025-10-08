<?php
/**
 * HTMX Helper Functions
 *
 * Utility functions for working with HTMX requests and responses
 */

/**
 * Check if the current request is from HTMX
 *
 * @return bool True if request is from HTMX
 */
function isHtmxRequest() {
    return isset($_SERVER['HTTP_HX_REQUEST']) && $_SERVER['HTTP_HX_REQUEST'] === 'true';
}

/**
 * Get the HTMX trigger element ID
 *
 * @return string|null The ID of the element that triggered the request
 */
function getHtmxTrigger() {
    return $_SERVER['HTTP_HX_TRIGGER'] ?? null;
}

/**
 * Get the HTMX target element ID
 *
 * @return string|null The ID of the target element
 */
function getHtmxTarget() {
    return $_SERVER['HTTP_HX_TARGET'] ?? null;
}

/**
 * Get the current URL from HTMX request
 *
 * @return string|null The current URL
 */
function getHtmxCurrentUrl() {
    return $_SERVER['HTTP_HX_CURRENT_URL'] ?? null;
}

/**
 * Send an HTMX redirect header
 *
 * @param string $url The URL to redirect to
 * @return void
 */
function htmxRedirect($url) {
    header('HX-Redirect: ' . $url);
    exit;
}

/**
 * Send an HTMX location header for client-side navigation
 *
 * @param string $url The URL to navigate to
 * @param array $options Additional options (target, swap, values)
 * @return void
 */
function htmxLocation($url, $options = []) {
    $location = ['path' => $url];
    if (!empty($options)) {
        $location = array_merge($location, $options);
    }
    header('HX-Location: ' . json_encode($location));
    exit;
}

/**
 * Trigger a client-side event
 *
 * @param string $eventName The name of the event to trigger
 * @param mixed $eventData Optional data to send with the event
 * @return void
 */
function htmxTrigger($eventName, $eventData = null) {
    if ($eventData !== null) {
        header('HX-Trigger: ' . json_encode([$eventName => $eventData]));
    } else {
        header('HX-Trigger: ' . $eventName);
    }
}

/**
 * Trigger multiple client-side events
 *
 * @param array $events Array of event names or associative array of event => data
 * @return void
 */
function htmxTriggerMultiple($events) {
    header('HX-Trigger: ' . json_encode($events));
}

/**
 * Trigger events after the swap phase
 *
 * @param string $eventName The name of the event
 * @param mixed $eventData Optional data
 * @return void
 */
function htmxTriggerAfterSwap($eventName, $eventData = null) {
    if ($eventData !== null) {
        header('HX-Trigger-After-Swap: ' . json_encode([$eventName => $eventData]));
    } else {
        header('HX-Trigger-After-Swap: ' . $eventName);
    }
}

/**
 * Trigger events after the settle phase
 *
 * @param string $eventName The name of the event
 * @param mixed $eventData Optional data
 * @return void
 */
function htmxTriggerAfterSettle($eventName, $eventData = null) {
    if ($eventData !== null) {
        header('HX-Trigger-After-Settle: ' . json_encode([$eventName => $eventData]));
    } else {
        header('HX-Trigger-After-Settle: ' . $eventName);
    }
}

/**
 * Force a page refresh
 *
 * @return void
 */
function htmxRefresh() {
    header('HX-Refresh: true');
    exit;
}

/**
 * Replace the current URL in browser history
 *
 * @param string $url The new URL
 * @return void
 */
function htmxPushUrl($url) {
    header('HX-Push-Url: ' . $url);
}

/**
 * Prevent browser history update
 *
 * @return void
 */
function htmxPreventPush() {
    header('HX-Push-Url: false');
}

/**
 * Control the swap behavior
 *
 * @param string $swapMethod The swap method (innerHTML, outerHTML, beforebegin, etc.)
 * @return void
 */
function htmxReswap($swapMethod) {
    header('HX-Reswap: ' . $swapMethod);
}

/**
 * Retarget the response to a different element
 *
 * @param string $selector CSS selector for the new target
 * @return void
 */
function htmxRetarget($selector) {
    header('HX-Retarget: ' . $selector);
}

/**
 * Send a success message to be displayed as a notification
 *
 * @param string $message The success message
 * @return void
 */
function htmxSuccess($message) {
    header('X-Success-Message: ' . $message);
}

/**
 * Send an error message to be displayed as a notification
 *
 * @param string $message The error message
 * @param int $statusCode HTTP status code (default 400)
 * @return void
 */
function htmxError($message, $statusCode = 400) {
    http_response_code($statusCode);
    header('X-Error-Message: ' . $message);
    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($message) . '</div>';
    exit;
}

/**
 * Render a partial template
 *
 * @param string $file The partial file name (without .php extension)
 * @param array $data Data to pass to the partial
 * @return void
 */
function renderPartial($file, $data = []) {
    $partialPath = __DIR__ . '/../partials/' . $file . '.php';

    if (!file_exists($partialPath)) {
        http_response_code(404);
        echo '<div class="alert alert-danger">Partial not found: ' . htmlspecialchars($file) . '</div>';
        exit;
    }

    // Extract data array to variables
    extract($data);

    // Include the partial
    include $partialPath;
}

/**
 * Return JSON response for HTMX (for compatibility)
 *
 * @param array $data The data to return
 * @param int $statusCode HTTP status code
 * @return void
 */
function htmxJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check CSRF token for HTMX requests
 *
 * @return bool True if valid
 */
function verifyHtmxCsrf() {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

    if (!$token || !isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token if not exists
 *
 * @return string The CSRF token
 */
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output CSRF hidden field for forms
 *
 * @return void
 */
function csrfField() {
    $token = getCsrfToken();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Check if request method matches
 *
 * @param string $method HTTP method (GET, POST, PUT, DELETE, etc.)
 * @return bool
 */
function isMethod($method) {
    return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
}

/**
 * Require specific HTTP method or return 405
 *
 * @param string|array $methods Allowed method(s)
 * @return void
 */
function requireMethod($methods) {
    $methods = (array) $methods;
    $currentMethod = $_SERVER['REQUEST_METHOD'];

    if (!in_array($currentMethod, $methods)) {
        http_response_code(405);
        header('Allow: ' . implode(', ', $methods));
        echo '<div class="alert alert-danger">Method not allowed</div>';
        exit;
    }
}

/**
 * Send HTML response with proper headers
 *
 * @param string $html The HTML to send
 * @param int $statusCode HTTP status code
 * @return void
 */
function htmxHtml($html, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    echo $html;
    exit;
}

/**
 * Get request parameter (from GET or POST)
 *
 * @param string $key The parameter key
 * @param mixed $default Default value if not found
 * @return mixed
 */
function getParam($key, $default = null) {
    return $_REQUEST[$key] ?? $default;
}

/**
 * Get multiple parameters
 *
 * @param array $keys Array of parameter keys
 * @param mixed $default Default value for missing keys
 * @return array
 */
function getParams($keys, $default = null) {
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = getParam($key, $default);
    }
    return $result;
}
