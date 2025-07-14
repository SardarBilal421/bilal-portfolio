# Contact Form Setup Guide

This guide will help you set up the contact form to send emails to your email address when someone fills out the form.

## Files Overview

- `contact.php` - Main contact form handler
- `config.php` - Configuration file for email settings
- `README.md` - This setup guide

## Quick Setup

### 1. Update Email Address

Edit `config.php` and change the receiving email address:

```php
'receiving_email' => 'your-email@gmail.com', // Change this to your email
```

### 2. Test the Form

1. Upload your files to a web server with PHP support
2. Fill out the contact form on your website
3. Check your email for the contact form submission

## Advanced Configuration

### Email Settings

In `config.php`, you can customize:

```php
$config = array(
    // Your email address
    'receiving_email' => 'your-email@gmail.com',

    // Your name (appears as sender)
    'your_name' => 'Your Name',

    // Website name (for email subject)
    'website_name' => 'Your Portfolio',

    // Enable/disable email sending
    'enable_email' => true,

    // Maximum message length
    'max_message_length' => 2000,

    // Rate limiting (prevent spam)
    'rate_limit_minutes' => 5,
    'rate_limit_attempts' => 3,
);
```

### Testing Mode

To test without sending emails, set:

```php
'enable_email' => false,
```

This will show a success message but won't actually send emails.

## Troubleshooting

### Emails Not Being Received

1. **Check Spam Folder** - Contact form emails often go to spam
2. **Server Configuration** - Ensure your hosting supports PHP mail()
3. **Email Limits** - Some hosting providers limit email sending

### Common Issues

#### "Failed to send email" Error

This usually means:

- PHP mail() function is disabled on your server
- Server email configuration is incorrect
- Email sending limits exceeded

**Solutions:**

1. Contact your hosting provider to enable PHP mail()
2. Use SMTP configuration (see below)
3. Check server error logs

#### Rate Limiting Issues

If users get "Too many attempts" errors:

- Increase `rate_limit_attempts` in config.php
- Increase `rate_limit_minutes` in config.php

## SMTP Configuration (Optional)

For better email delivery, you can configure SMTP:

1. **Enable SMTP** in `config.php`:

```php
'use_smtp' => true,
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_username' => 'your-email@gmail.com',
'smtp_password' => 'your-app-password',
'smtp_secure' => 'tls',
```

2. **For Gmail:**

   - Enable 2-factor authentication
   - Generate an App Password
   - Use the App Password instead of your regular password

3. **For other providers:**
   - Check your email provider's SMTP settings
   - Update the host, port, and security settings accordingly

## Security Features

### Rate Limiting

- Prevents spam by limiting submissions per IP
- Configurable time period and attempt limits

### Input Validation

- Sanitizes all form inputs
- Validates email format
- Checks message length

### reCAPTCHA (Optional)

To enable Google reCAPTCHA:

1. Get reCAPTCHA keys from https://www.google.com/recaptcha/
2. Update `config.php`:

```php
'use_recaptcha' => true,
'recaptcha_site_key' => 'your-site-key',
'recaptcha_secret_key' => 'your-secret-key',
```

3. Add reCAPTCHA to your HTML form (see below)

## Adding reCAPTCHA to Form

If you enable reCAPTCHA, add this to your HTML form:

```html
<!-- Add in <head> section -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!-- Add before submit button -->
<div class="g-recaptcha" data-sitekey="your-site-key"></div>
```

## Logging

The system logs all form submissions and errors to:

- `contact_form.log` - General form activity
- `rate_limit.log` - Rate limiting activity

Check these files if you're having issues.

## File Permissions

Ensure the `forms` directory has write permissions for logging:

```bash
chmod 755 forms/
chmod 644 forms/*.php
```

## Testing

1. **Local Testing:**

   - Use XAMPP, WAMP, or similar local server
   - Set `enable_email` to `false` for testing

2. **Live Testing:**
   - Upload to your web server
   - Set `enable_email` to `true`
   - Fill out the form and check your email

## Support

If you're still having issues:

1. Check the log files in the `forms` directory
2. Verify your hosting supports PHP mail()
3. Contact your hosting provider for email configuration help
4. Consider using a third-party email service like SendGrid or Mailgun

## Security Notes

- Keep `config.php` secure and don't share SMTP passwords
- Regularly check log files for suspicious activity
- Consider using HTTPS for your website
- Update the rate limiting settings based on your needs
