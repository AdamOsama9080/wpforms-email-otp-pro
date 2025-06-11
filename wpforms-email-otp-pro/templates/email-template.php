<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Verification Code</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .otp-code { 
            font-size: 24px; 
            font-weight: bold; 
            text-align: center; 
            margin: 20px 0; 
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
            letter-spacing: 3px;
        }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <h2><?php _e('Your Verification Code', 'wpforms-email-otp-pro'); ?></h2>
        </div>
        
        <p><?php _e('Please use the following verification code to complete your registration:', 'wpforms-email-otp-pro'); ?></p>
        
        <div class="otp-code"><?php echo esc_html($otp); ?></div>
        
        <p><?php _e('This code will expire in 5 minutes. If you didn\'t request this code, please ignore this email.', 'wpforms-email-otp-pro'); ?></p>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>