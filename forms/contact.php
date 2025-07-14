<?php
// Contact Form Handler
// This script handles the contact form submission and sends emails

// Include configuration
require_once 'config.php';

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
  exit;
}

// Function to sanitize input data
function sanitize_input($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Function to validate email
function is_valid_email($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

try {
  // Check rate limiting
  $ip_address = $_SERVER['REMOTE_ADDR'];
  if (!check_rate_limit($ip_address)) {
    http_response_code(429);
    echo json_encode([
      'status' => 'error',
      'message' => 'Too many attempts. Please wait a few minutes before trying again.'
    ]);
    exit;
  }

  // Get and validate form data
  $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
  $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
  $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : '';
  $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
  $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

  // Validation
  $errors = [];

  if (empty($name)) {
    $errors[] = 'Name is required';
  }

  if (empty($email)) {
    $errors[] = 'Email is required';
  } elseif (!is_valid_email($email)) {
    $errors[] = 'Please enter a valid email address';
  }

  if (empty($subject)) {
    $errors[] = 'Subject is required';
  }

  if (empty($message)) {
    $errors[] = 'Message is required';
  } elseif (strlen($message) > get_config('max_message_length', 2000)) {
    $errors[] = 'Message is too long. Maximum ' . get_config('max_message_length', 2000) . ' characters allowed.';
  }

  // Validate reCAPTCHA if enabled
  if (get_config('use_recaptcha') && !validate_recaptcha($recaptcha_response)) {
    $errors[] = 'Please complete the reCAPTCHA verification';
  }

  // If there are validation errors, return them
  if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
      'status' => 'error',
      'message' => 'Validation failed',
      'errors' => $errors
    ]);
    exit;
  }

  // Check if email sending is enabled
  if (!get_config('enable_email')) {
    // For testing purposes, just return success
    echo json_encode([
      'status' => 'success',
      'message' => 'Your message has been received! (Email sending is disabled for testing)'
    ]);
    log_message("Contact form submitted (email disabled) from: $email - $name");
    exit;
  }

  // Prepare email content
  $website_name = get_config('website_name', 'Portfolio Website');
  $email_subject = "$website_name - New Contact Form Submission: " . $subject;

  $email_body = "
    <html>
    <head>
        <title>New Contact Form Submission</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #173b6c; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #173b6c; }
            .value { padding: 10px; background-color: white; border: 1px solid #ddd; border-radius: 4px; }
            .footer { margin-top: 20px; padding: 15px; background-color: #eee; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>New Contact Form Submission</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Name:</div>
                    <div class='value'>" . htmlspecialchars($name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>" . htmlspecialchars($email) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Subject:</div>
                    <div class='value'>" . htmlspecialchars($subject) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p><strong>Submitted on:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>IP Address:</strong> " . $ip_address . "</p>
                <p><strong>User Agent:</strong> " . $_SERVER['HTTP_USER_AGENT'] . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

  // Email headers
  $receiving_email = get_config('receiving_email');
  $your_name = get_config('your_name', 'Portfolio Website');

  $headers = array();
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=UTF-8';
  $headers[] = 'From: ' . $your_name . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>';
  $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
  $headers[] = 'X-Mailer: PHP/' . phpversion();
  $headers[] = 'X-Priority: 1';

  // Send email using PHP mail() function
  $email_sent = mail($receiving_email, $email_subject, $email_body, implode("\r\n", $headers));

  if ($email_sent) {
    // Success response
    echo json_encode([
      'status' => 'success',
      'message' => 'Your message has been sent successfully! I will get back to you soon.'
    ]);

    // Log successful submission
    log_message("Contact form submitted successfully from: $email - $name", 'success');
  } else {
    // Failed to send email
    http_response_code(500);
    echo json_encode([
      'status' => 'error',
      'message' => 'Failed to send email. Please try again later or contact me directly at ' . $receiving_email
    ]);

    // Log error
    log_message("Failed to send contact form email from: $email - $name", 'error');
  }
} catch (Exception $e) {
  // Handle any unexpected errors
  http_response_code(500);
  echo json_encode([
    'status' => 'error',
    'message' => 'An unexpected error occurred. Please try again later.'
  ]);

  // Log error
  log_message("Contact form error: " . $e->getMessage(), 'error');
}
