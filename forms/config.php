<?php
// Contact Form Configuration
// Update these settings according to your needs

// Email Configuration
$config = array(
    // Your email address where you want to receive contact form submissions
    'receiving_email' => 'sardar142bilal@gmail.com',

    // Your name (will appear as sender name)
    'your_name' => 'Bilal Rehman',

    // Website name (for email subject prefix)
    'website_name' => 'Bilal Rehman Portfolio',

    // Enable/disable email sending (set to false for testing)
    'enable_email' => true,

    // Enable/disable logging
    'enable_logging' => true,

    // Maximum message length (in characters)
    'max_message_length' => 2000,

    // Rate limiting (prevent spam)
    'rate_limit_minutes' => 5, // minutes
    'rate_limit_attempts' => 3, // maximum attempts per time period

    // SMTP Configuration (optional - for better email delivery)
    'use_smtp' => false,
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',
    'smtp_secure' => 'tls',

    // reCAPTCHA Configuration (optional - for spam protection)
    'use_recaptcha' => false,
    'recaptcha_site_key' => 'your-recaptcha-site-key',
    'recaptcha_secret_key' => 'your-recaptcha-secret-key',
);

// Function to get configuration value
function get_config($key, $default = null)
{
    global $config;
    return isset($config[$key]) ? $config[$key] : $default;
}

// Function to log messages
function log_message($message, $type = 'info')
{
    if (!get_config('enable_logging')) {
        return;
    }

    $log_file = __DIR__ . '/contact_form.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;

    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Function to check rate limiting
function check_rate_limit($ip_address)
{
    $rate_limit_minutes = get_config('rate_limit_minutes', 5);
    $rate_limit_attempts = get_config('rate_limit_attempts', 3);

    $log_file = __DIR__ . '/rate_limit.log';
    $current_time = time();
    $cutoff_time = $current_time - ($rate_limit_minutes * 60);

    // Read existing log entries
    $entries = [];
    if (file_exists($log_file)) {
        $entries = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    // Filter entries for this IP and time period
    $recent_entries = [];
    foreach ($entries as $entry) {
        $parts = explode('|', $entry);
        if (count($parts) >= 2) {
            $timestamp = (int)$parts[0];
            $ip = $parts[1];

            if ($ip === $ip_address && $timestamp > $cutoff_time) {
                $recent_entries[] = $entry;
            }
        }
    }

    // Check if limit exceeded
    if (count($recent_entries) >= $rate_limit_attempts) {
        return false; // Rate limit exceeded
    }

    // Add current attempt
    $new_entry = $current_time . '|' . $ip_address . PHP_EOL;
    file_put_contents($log_file, $new_entry, FILE_APPEND | LOCK_EX);

    return true; // Rate limit not exceeded
}

// Function to validate reCAPTCHA
function validate_recaptcha($recaptcha_response)
{
    if (!get_config('use_recaptcha')) {
        return true; // Skip validation if not enabled
    }

    $secret_key = get_config('recaptcha_secret_key');
    if (empty($secret_key)) {
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => $secret_key,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    );

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    return isset($response['success']) && $response['success'] === true;
}
