# Hostinger Deployment Guide

## Step-by-Step Guide to Deploy SAMPARK on Hostinger

### 1. Prepare Your Files

1. **Update Configuration Files:**
   - Copy `config/config.production.php` to `config/config.php`
   - Update `config/email_config.php` with your Hostinger email details

2. **Update Email Configuration:**
   Edit `config/email_config.php`:
   ```php
   define('SMTP_USERNAME', 'noreply@yourdomain.com'); // Your Hostinger email
   define('SMTP_PASSWORD', 'your-email-password'); // Your email password
   define('EMAIL_FROM', 'noreply@yourdomain.com'); // Your Hostinger email
   define('EMAIL_REPLY_TO', 'support@yourdomain.com'); // Support email
   ```

### 2. Create Email Account in Hostinger

1. **Login to Hostinger Control Panel**
2. **Go to Email → Email Accounts**
3. **Create New Email Account:**
   - Email: `noreply@yourdomain.com`
   - Password: Create a strong password
   - Mailbox size: 1GB (or as needed)
4. **Note down the email credentials**

### 3. Upload Files to Hostinger

1. **Access File Manager in Hostinger Control Panel**
2. **Navigate to public_html directory**
3. **Upload all project files maintaining the structure:**
   ```
   public_html/
   ├── config/
   ├── src/
   ├── public/
   ├── logs/
   └── uploads/
   ```

### 4. Set File Permissions

Set the following permissions:
- `logs/` directory: 755
- `uploads/` directory: 755
- `config/` directory: 644 (for config files)

### 5. Update BASE_URL

In `config/config.php`, update the BASE_URL:
```php
define('BASE_URL', '/'); // If in root directory
// OR
define('BASE_URL', '/subfolder/'); // If in subfolder
```

### 6. Test Email Functionality

1. **Access the test script:**
   ```
   https://yourdomain.com/test_email.php
   ```

2. **Update the test email address in the script**
3. **Run the test**
4. **Check if email is received**

### 7. Configure Domain (if needed)

1. **Point your domain to Hostinger nameservers**
2. **Wait for DNS propagation (24-48 hours)**
3. **Set up SSL certificate (free with Hostinger)**

### 8. Final Configuration

1. **Delete test files:**
   - Remove `public/test_email.php` after successful testing

2. **Update any remaining configuration:**
   - Database settings (if using Hostinger database)
   - File paths
   - Email settings

### 9. Test the Application

1. **Test login functionality**
2. **Test complaint submission**
3. **Test email functionality**
4. **Test file uploads**

## Troubleshooting

### Email Issues

1. **Check Hostinger email settings:**
   - SMTP Host: `smtp.hostinger.com`
   - SMTP Port: `587` (TLS) or `465` (SSL)
   - Username: Your Hostinger email
   - Password: Your email password

2. **Check email limits:**
   - Hostinger typically allows 100-500 emails per hour
   - Check your hosting plan limits

3. **Check error logs:**
   - View logs in Hostinger control panel
   - Check application logs in `logs/` directory

### Database Issues

1. **If using Hostinger database:**
   - Update database credentials in `config/config.php`
   - Import your database schema
   - Test database connection

2. **If using external database:**
   - Ensure database server allows external connections
   - Check firewall settings

### File Upload Issues

1. **Check upload directory permissions:**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/evidences/
   ```

2. **Check PHP upload limits:**
   - `upload_max_filesize` in php.ini
   - `post_max_size` in php.ini

### Performance Optimization

1. **Enable Hostinger caching:**
   - Use Hostinger's built-in caching
   - Enable GZIP compression

2. **Optimize images:**
   - Compress uploaded images
   - Use appropriate image formats

3. **Database optimization:**
   - Add indexes to frequently queried columns
   - Optimize database queries

## Security Checklist

- [ ] Remove test files
- [ ] Set proper file permissions
- [ ] Enable SSL certificate
- [ ] Use strong passwords
- [ ] Keep software updated
- [ ] Regular backups
- [ ] Monitor error logs

## Support

If you encounter issues:

1. **Check Hostinger knowledge base**
2. **Contact Hostinger support**
3. **Check application error logs**
4. **Verify all configuration settings**

## Email Configuration Reference

### Hostinger SMTP Settings:
- **SMTP Host:** `smtp.hostinger.com`
- **SMTP Port:** `587` (TLS) or `465` (SSL)
- **Security:** TLS/SSL
- **Authentication:** Required
- **Username:** Your Hostinger email
- **Password:** Your email password

### Email Limits:
- **Shared Hosting:** 100-500 emails/hour
- **Cloud Hosting:** Higher limits
- **VPS/Dedicated:** No limits

## Maintenance

1. **Regular backups:**
   - Database backups
   - File backups
   - Configuration backups

2. **Monitor logs:**
   - Error logs
   - Email logs
   - Access logs

3. **Update software:**
   - PHP version
   - Application updates
   - Security patches
