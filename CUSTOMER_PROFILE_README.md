# Profile Pages

## Overview
The system now has separate profile pages for different user types to avoid clutter and better handle specific inputs:

1. **Customer Profile Page** - For customers only
2. **Staff Profile Page** - For admin, controller, and viewer users

Both pages are fully responsive and follow the existing design patterns of the SAMPARK Railway Grievance System.

## Customer Profile Page

### Overview
The Customer Profile Page allows customers to view and edit their account information, including personal details and password changes.

## Features

### 1. Personal Information Management
- **Name**: Full name of the customer
- **Email**: Email address for communication
- **Mobile Number**: Contact number with automatic formatting
- **Company Name**: Organization/company name
- **Designation**: Job title or position
- **Customer ID**: Read-only field showing unique customer identifier

### 2. Password Management
- **Current Password Verification**: Required to change password
- **New Password**: Minimum 6 characters
- **Confirm Password**: Must match new password
- **Real-time Validation**: Visual feedback for password requirements
- **Show/Hide Toggle**: Eye icon to toggle password visibility

### 3. Security Features
- **Password Hashing**: All passwords are securely hashed using PHP's `password_hash()`
- **Input Validation**: Server-side and client-side validation
- **CSRF Protection**: Built-in protection against cross-site request forgery
- **Session Management**: Secure session handling

### 4. User Experience
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with animations
- **Real-time Feedback**: Instant validation and success/error messages
- **Loading States**: Visual feedback during form submission
- **Auto-hide Alerts**: Success/error messages automatically disappear

## Staff Profile Page

### Overview
The Staff Profile Page allows staff users (admin, controller, viewer) to view and edit their account information, including personal details and password changes.

### Features

#### 1. Personal Information Management
- **Name**: Full name of the staff member
- **Email**: Email address for communication
- **Mobile Number**: Contact number with automatic formatting
- **Department**: Dropdown selection for department assignment
- **Division**: Division/region information
- **Zone**: Zone information
- **Login ID**: Read-only field showing unique login identifier
- **Role**: Read-only field showing user role (Administrator, Controller, Viewer)

#### 2. Password Management
- **Current Password Verification**: Required to change password
- **New Password**: Minimum 6 characters
- **Confirm Password**: Must match new password
- **Real-time Validation**: Visual feedback for password requirements
- **Show/Hide Toggle**: Eye icon to toggle password visibility

#### 3. Security Features
- **Password Hashing**: All passwords are securely hashed using PHP's `password_hash()`
- **Input Validation**: Server-side and client-side validation
- **CSRF Protection**: Built-in protection against cross-site request forgery
- **Session Management**: Secure session handling

#### 4. User Experience
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with animations
- **Real-time Feedback**: Instant validation and success/error messages
- **Loading States**: Visual feedback during form submission
- **Auto-hide Alerts**: Success/error messages automatically disappear

## File Structure

```
public/
├── pages/
│   ├── profile.php              # Customer profile page
│   └── staff_profile.php        # Staff profile page
├── css/
│   └── profile.css              # Profile page styles (shared)
└── js/
    └── profile.js               # Profile page JavaScript (shared)
```

## Access Control & Routing

### User Type Detection
- **Customers**: Automatically redirected to `/profile`
- **Staff Users**: Automatically redirected to `/staff-profile`
- **Unauthorized Access**: Redirected to appropriate profile page based on role

### Routes
- `/profile` - Customer profile page (customers only)
- `/staff-profile` - Staff profile page (admin, controller, viewer only)

## Technical Implementation

### Backend (PHP)
- **PageController::profile()**: Handles customer profile page routing and data loading
- **PageController::staffProfile()**: Handles staff profile page routing and data loading
- **User Model**: Manages user table operations
- **Customer Model**: Manages customer table operations
- **SessionManager**: Handles authentication and session data

### Frontend (HTML/CSS/JS)
- **Bootstrap 5**: Responsive grid system and components
- **Font Awesome**: Icons for better visual experience
- **Custom CSS**: Modern styling with gradients and animations
- **Vanilla JavaScript**: Form validation and interactive features

## Database Integration

### User Table Updates
- Updates `name`, `email`, `mobile` fields
- Maintains `updated_at` timestamp

### Customer Table Updates
- Updates `Name`, `Email`, `MobileNumber`, `CompanyName`, `Designation` fields
- Handles password updates with proper hashing

## Security Considerations

1. **Password Security**
   - All passwords are hashed using `PASSWORD_DEFAULT`
   - Current password verification before changes
   - Minimum password length enforcement

2. **Input Validation**
   - Server-side validation for all inputs
   - Client-side validation for immediate feedback
   - SQL injection prevention through prepared statements

3. **Session Security**
   - Login requirement for access
   - Session timeout handling
   - CSRF token protection

## Responsive Design

### Desktop (992px+)
- Two-column layout with sidebar
- Full-width forms
- Sticky sidebar navigation

### Tablet (768px - 991px)
- Single-column layout
- Stacked form fields
- Responsive button sizing

### Mobile (767px and below)
- Single-column layout
- Full-width buttons
- Optimized touch targets
- Prevented zoom on input focus

## Browser Compatibility

- **Chrome**: Full support
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **Mobile browsers**: Full support

## Usage Instructions

### For Customers
1. Navigate to the profile page via the user dropdown in the navbar
2. Update personal information in the "Personal Information" section
3. Click "Update Profile" to save changes
4. To change password, fill in the "Change Password" section
5. Click "Change Password" to update password

### For Developers
1. The profile page is automatically accessible to logged-in users
2. No additional configuration required
3. CSS and JS files are automatically loaded
4. All validation and security measures are built-in

## Error Handling

- **Validation Errors**: Displayed as red alerts
- **Success Messages**: Displayed as green alerts
- **System Errors**: Generic error messages for security
- **Auto-hide**: All alerts disappear after 5 seconds

## Performance Optimizations

- **Debounced Input**: Mobile number formatting
- **Lazy Loading**: CSS and JS loaded only when needed
- **Minimal DOM Manipulation**: Efficient JavaScript
- **Optimized Animations**: Hardware-accelerated CSS transitions

## Future Enhancements

1. **Profile Picture Upload**: Add avatar functionality
2. **Two-Factor Authentication**: Enhanced security
3. **Activity Log**: Track profile changes
4. **Email Verification**: Confirm email changes
5. **Social Login Integration**: Google, Facebook login options

## Troubleshooting

### Common Issues
1. **Password not updating**: Ensure current password is correct
2. **Form not submitting**: Check for validation errors
3. **Mobile number formatting**: Automatically formats as XXX-XXX-XXXX
4. **Responsive issues**: Clear browser cache and reload

### Debug Mode
- Check browser console for JavaScript errors
- Verify PHP error logs for backend issues
- Ensure all required files are accessible

## Dependencies

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5.0+
- Font Awesome 5.0+
- Modern web browser with JavaScript enabled
