# SAMPARK FOIS - Railway Complaint Management System

This is a comprehensive railway complaint management system designed to streamline the process of handling freight-related customer complaints for Central Railway.

## Features

- **Customer Portal**: Easy complaint submission with evidence upload
- **Staff Dashboard**: Complaint management and tracking
- **Multi-Department Routing**: Automatic forwarding to appropriate departments
- **Real-time Tracking**: Live status updates and notifications
- **Evidence Management**: Support for image uploads (up to 3 images per complaint)
- **Role-based Access**: Customer, Employee, Officer, and Admin roles
- **Mobile Responsive**: Works on all devices
- **Secure**: Password hashing and session management

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Apache/Nginx web server
- PHP Extensions: PDO, PDO_MySQL, GD, JSON

## Installation

1. **Clone/Download the project** to your web server directory

2. **Database Setup**:
   - Import the database schema from `u473452443_sampark.sql`
   - Update database credentials in `config/config.php`

3. **Initialize Database**:
   ```bash
   php init.php
   ```

4. **Set Permissions**:
   - Ensure `public/uploads/evidences/` is writable
   - Set appropriate file permissions (755 for directories, 644 for files)

5. **Configuration**:
   - Update `BASE_URL` in `config/config.php` if needed
   - Configure timezone settings
   - Set upload limits and allowed file types

## Default Login Credentials

After running `init.php`, the following default accounts are created:

- **Admin**: 
  - Username: `admin`
  - Password: `admin123`

- **Commercial Officer**: 
  - Username: `commercial_officer`
  - Password: `commercial123`

**⚠️ Important**: Change these default passwords immediately after installation!

## Directory Structure

```
samadhanFOIS/
├── config/
│   └── config.php              # Database and app configuration
├── src/
│   ├── api/                    # API endpoints
│   ├── controllers/            # MVC Controllers
│   ├── models/                 # Database models
│   └── views/                  # Header/Footer templates
├── public/
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript files
│   ├── images/                 # Static images
│   ├── uploads/evidences/      # Uploaded complaint evidence
│   ├── pages/                  # Page templates
│   └── index.php               # Front controller
├── init.php                    # Database initialization
└── u473452443_sampark.sql      # Database schema
```

## Database Schema

### Tables Created:

1. **customers** - Customer information (pre-populated)
2. **users** - System users with roles and authentication
3. **complaints** - Main complaint data
4. **transactions** - Complaint activity/history tracking
5. **evidence** - File upload references

### Relationships:
- Users can be linked to customers
- Complaints belong to customers and can be assigned to users
- Transactions track all complaint modifications
- Evidence stores file paths for complaint images

## Usage

### For Customers:
1. Register or login to the system
2. Click "New Complaint" to submit a complaint
3. Fill in details and upload evidence (optional)
4. Track complaint status from dashboard
5. Receive updates as complaint progresses

### For Staff/Officers:
1. Login with employee credentials
2. View assigned complaints from dashboard
3. Update complaint status and add remarks
4. Forward complaints to other departments
5. Mark complaints as resolved

### For Administrators:
1. Manage user accounts and roles
2. View system-wide reports
3. Monitor complaint statistics
4. Manage system settings

## API Endpoints

The system includes RESTful APIs for:

- `/api/auth` - Authentication
- `/api/complaints` - Complaint management
- `/api/users` - User management
- `/api/transactions` - Activity tracking
- `/api/evidence` - File uploads
- `/api/notifications` - Real-time notifications
- `/api/dashboard` - Dashboard data
- `/api/reports` - Reporting

## Security Features

- Password hashing with PHP's `password_hash()`
- Session-based authentication
- SQL injection prevention with prepared statements
- CSRF protection
- File upload validation
- Role-based access control
- Input sanitization

## File Upload Guidelines

- **Allowed formats**: JPG, JPEG, PNG, GIF
- **Maximum file size**: 5MB per file
- **Maximum files**: 3 images per complaint
- **Storage**: Files stored in `public/uploads/evidences/`
- **Validation**: File type and size validation

## Timezone Configuration

The system is configured for Indian Standard Time (Asia/Kolkata). Both PHP and MySQL timestamps are synchronized to IST regardless of server timezone.

## Hosting on Hostinger

This system is optimized for Hostinger Cloud Hosting:

1. Upload files to `public_html` directory
2. Create MySQL database and user
3. Import the SQL schema
4. Update `config/config.php` with your database credentials
5. Run `init.php` to initialize the system

## Maintenance

### Regular Tasks:
- Monitor log files for errors
- Clean up old uploaded files
- Backup database regularly
- Update user passwords periodically

### Database Maintenance:
```bash
# Verify system setup
php init.php verify

# Get database statistics
php init.php
```

## Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Check database credentials in `config/config.php`
   - Ensure MySQL service is running

2. **File Upload Issues**:
   - Check folder permissions for `public/uploads/evidences/`
   - Verify PHP `upload_max_filesize` and `post_max_size` settings

3. **Session Issues**:
   - Clear browser cookies
   - Check PHP session configuration

4. **Timezone Issues**:
   - System automatically handles IST conversion
   - Verify timezone setting in `config/config.php`

## Support

For technical support or issues:
- Email: admin@railway.gov.in
- Phone: +91 12345 67890

## Version

Current Version: 1.0

## License

This system is developed for Central Railway, Ministry of Railways, Government of India.

---

**Note**: This system contains sensitive configuration files. Ensure proper security measures are in place before deploying to production.

**Note for developers:** After cloning, you might need to adjust file permissions for the `logs` and `public/uploads` directories to make them writable by the web server.
