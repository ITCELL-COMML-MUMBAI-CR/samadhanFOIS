# Bulk Email Management - SAMPARK FOIS

## Overview
The Bulk Email Management feature allows administrators to send emails to multiple users simultaneously. This feature is designed to facilitate communication with system users for announcements, updates, and notifications.

## Features

### 1. Recipient Selection
- **Send to All Users**: Send emails to all active users in the system
- **Select Specific Users**: Choose individual users from a list
- **User Information Display**: Shows user name, email, role, and department

### 2. Email Templates
The system includes several pre-built email templates:

#### SAMPARK Portal Invitation
- **Purpose**: Welcome new users to the SAMPARK FOIS portal
- **Content**: Introduces portal features and login details
- **Placeholders**: {name}, {login_id}, {portal_url}

#### System Maintenance Notice
- **Purpose**: Notify users about scheduled maintenance
- **Content**: Maintenance details and affected services
- **Placeholders**: {name}, {maintenance_date}, {maintenance_time}
- **Custom Variables**: Date and time fields for maintenance scheduling

#### Policy Update Notification
- **Purpose**: Inform users about policy changes
- **Content**: Key updates and guidelines
- **Placeholders**: {name}

#### Custom Email
- **Purpose**: Create completely custom emails
- **Content**: Fully editable subject and content
- **Placeholders**: All available placeholders

### 3. Email Customization
- **Subject Line**: Customizable email subject
- **Content**: Rich HTML content with placeholders
- **CC Field**: Optional carbon copy recipients
- **Template Variables**: Dynamic content for specific templates

### 4. Available Placeholders
The following placeholders can be used in email content:
- `{name}` - User's full name
- `{login_id}` - User's login ID
- `{email}` - User's email address
- `{department}` - User's department
- `{role}` - User's role in the system
- `{portal_url}` - SAMPARK FOIS portal URL

### 5. Preview and Testing
- **Email Preview**: View how the email will appear before sending
- **Test Email**: Send a test email to verify content and formatting
- **Real-time Validation**: Form validation with helpful error messages

## How to Use

### Accessing Bulk Email
1. Log in as an administrator
2. Navigate to **Administration** → **Bulk Email** in the navigation menu
3. Or directly visit: `/admin/bulk-email`

### Sending a Bulk Email

#### Step 1: Select Recipients
1. Choose between "Send to All Users" or "Select Specific Users"
2. If selecting specific users, use the checkboxes to choose recipients
3. Use "Select All" or "Deselect All" buttons for quick selection

#### Step 2: Choose Template (Optional)
1. Select a template from the dropdown menu
2. The subject and content will be automatically populated
3. For maintenance notices, fill in the date and time fields
4. Modify the content as needed

#### Step 3: Customize Email
1. Edit the subject line if needed
2. Modify the email content
3. Add CC recipients if required
4. Use placeholders for personalized content

#### Step 4: Preview and Test
1. Click "Preview" to see how the email will appear
2. Click "Send Test" to send a test email to verify functionality
3. Review the content and formatting

#### Step 5: Send Email
1. Click "Send Bulk Email"
2. Confirm the action when prompted
3. Monitor the success/error messages

## Best Practices

### Content Guidelines
- Keep subject lines clear and concise
- Use professional language and formatting
- Test emails before sending to all users
- Use placeholders for personalization
- Keep content relevant and actionable

### Technical Considerations
- Large recipient lists may take time to process
- Emails are sent with a small delay between recipients to prevent server overload
- Failed emails are logged for review
- Test emails are sent immediately without delays

### Security and Privacy
- Only administrators can access this feature
- Email addresses are not shared between recipients
- All email sending is logged for audit purposes
- CC recipients are visible to all recipients

## Troubleshooting

### Common Issues

#### Email Not Sending
- Check if the mail server is configured properly
- Verify recipient email addresses are valid
- Check system logs for error messages
- Ensure the user has administrator privileges

#### Template Not Loading
- Refresh the page and try again
- Check browser console for JavaScript errors
- Verify all required files are loaded

#### Recipients Not Receiving Emails
- Check spam/junk folders
- Verify email server configuration
- Review system logs for delivery status
- Test with a single recipient first

### Error Messages
- **"Missing required fields"**: Fill in subject and content
- **"No valid recipients found"**: Select at least one user
- **"Invalid email address"**: Check email format in CC field
- **"Access denied"**: Ensure you have administrator privileges

## Technical Details

### File Structure
```
public/pages/admin_bulk_email.php    # Main page interface
public/css/bulk_email.css            # Styling and responsive design
public/js/bulk_email.js              # Client-side functionality
src/api/bulk_email.php               # API endpoint for email sending
src/controllers/AdminController.php  # Controller handling the page
src/utils/EmailService.php           # Email service with custom email support
src/models/User.php                  # User model with bulk user methods
```

### Database Requirements
- Users table with email addresses
- Active user status tracking
- User role and department information

### Email Configuration
- SMTP settings for production environment
- Fallback to PHP mail() function
- HTML email templates with responsive design
- Proper headers and encoding

## Support

For technical support or questions about the bulk email feature:
- Contact: sampark-admin@itcellbbcr.in
- Reference: SAMPARK FOIS Bulk Email System
- Include error messages and steps to reproduce issues
