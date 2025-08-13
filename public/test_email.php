<?php
/**
 * Email Test Script for Hostinger
 * Use this to test email functionality after deployment
 */

// Include configuration
require_once '../config/config.php';
require_once '../src/utils/EmailService.php';

// Set environment to production for testing
define('ENVIRONMENT', 'production');

echo "<h1>Email Test for Hostinger</h1>";
echo "<p>Testing email functionality...</p>";

try {
    $emailService = new EmailService();
    
    // Test data
    $testEmail = 'your-test-email@gmail.com'; // Replace with your email
    $testName = 'Test User';
    $testComplaintId = 'TEST' . date('YmdHis');
    $testComplaintDetails = [
        'complaint_type' => 'Test Type',
        'complaint_subtype' => 'Test Subtype',
        'category' => 'Test Category',
        'location' => 'Test Location',
        'department' => 'Test Department'
    ];
    
    echo "<p>Attempting to send test email to: <strong>$testEmail</strong></p>";
    
    // Test complaint confirmation email
    $result = $emailService->sendComplaintConfirmation(
        $testEmail,
        $testName,
        $testComplaintId,
        $testComplaintDetails
    );
    
    if ($result) {
        echo "<div style='color: green; padding: 10px; border: 1px solid green; background: #d4edda;'>";
        echo "✅ <strong>SUCCESS!</strong> Email sent successfully!";
        echo "<br>Check your email inbox for the test message.";
        echo "<br>Complaint ID: <strong>$testComplaintId</strong>";
        echo "</div>";
    } else {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #f8d7da;'>";
        echo "❌ <strong>FAILED!</strong> Email failed to send.";
        echo "<br>Check the error logs for more details.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background: #f8d7da;'>";
    echo "❌ <strong>ERROR!</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>Configuration Check:</h3>";
echo "<ul>";
echo "<li>Environment: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'Not defined') . "</li>";
echo "<li>SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'Not configured') . "</li>";
echo "<li>SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'Not configured') . "</li>";
echo "<li>Email From: " . (defined('EMAIL_FROM') ? EMAIL_FROM : 'Not configured') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Update <code>config/email_config.php</code> with your Hostinger email details</li>";
echo "<li>Create an email account in Hostinger control panel</li>";
echo "<li>Replace 'your-test-email@gmail.com' with your actual email</li>";
echo "<li>Run this test again</li>";
echo "<li>Delete this file after successful testing</li>";
echo "</ol>";

echo "<p><strong>Note:</strong> This file should be deleted after testing for security reasons.</p>";
?>
