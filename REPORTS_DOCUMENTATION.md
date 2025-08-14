# 📊 SAMPARK FOIS - Comprehensive Reports & Analytics System

## Overview

The SAMPARK FOIS Reports System provides comprehensive analytics and reporting capabilities for the Railway Complaint Management System. It includes interactive charts, pivot tables, MIS reports, and performance metrics to help administrators and controllers make data-driven decisions.

## Features

### 🎯 Dashboard Overview
- **Summary Cards**: Total complaints, resolved, pending, and resolution rate
- **Status Distribution**: Doughnut chart showing complaint status breakdown
- **Priority Distribution**: Bar chart showing complaint priority levels
- **Department Analysis**: Pie chart showing complaints by department
- **Category Analysis**: Horizontal bar chart showing top complaint categories
- **Timeline Chart**: Line chart showing complaints over time (daily/weekly/monthly)

### 📈 MIS Reports
- **Executive Summary**: High-level overview with key metrics
- **Department Performance**: Detailed performance analysis by department
- **Top Issues**: Most common complaint types and their percentages
- **Monthly Trends**: Historical data showing complaint patterns

### ⚡ Performance Metrics
- **Resolution Time**: Average, minimum, and maximum resolution times
- **Response Time**: Average response time in hours
- **Satisfaction Rate**: Resolution rate as a proxy for satisfaction
- **User Activity**: Detailed analysis of user actions and productivity

### 🔄 Pivot Table Analysis
- **Dynamic Pivoting**: Analyze data by any combination of dimensions
- **Multiple Views**: Rows, columns, and values can be customized
- **Real-time Updates**: Instant recalculation when parameters change

## Technical Implementation

### API Endpoints

The reports system uses RESTful API endpoints located in `src/api/reports.php`:

#### Dashboard Statistics
```
GET /src/api/reports.php?action=dashboard_stats&date_from=2024-01-01&date_to=2024-01-31
```

#### Status Analysis
```
GET /src/api/reports.php?action=complaints_by_status&date_from=2024-01-01&date_to=2024-01-31
```

#### Priority Analysis
```
GET /src/api/reports.php?action=complaints_by_priority&date_from=2024-01-01&date_to=2024-01-31
```

#### Department Analysis
```
GET /src/api/reports.php?action=complaints_by_department&date_from=2024-01-01&date_to=2024-01-31
```

#### Category Analysis
```
GET /src/api/reports.php?action=complaints_by_category&date_from=2024-01-01&date_to=2024-01-31
```

#### Timeline Analysis
```
GET /src/api/reports.php?action=complaints_timeline&date_from=2024-01-01&date_to=2024-01-31&group_by=day
```

#### Performance Metrics
```
GET /src/api/reports.php?action=performance_metrics&date_from=2024-01-01&date_to=2024-01-31
```

#### User Activity
```
GET /src/api/reports.php?action=user_activity&date_from=2024-01-01&date_to=2024-01-31
```

#### MIS Report
```
GET /src/api/reports.php?action=mis_report&date_from=2024-01-01&date_to=2024-01-31
```

#### Pivot Table
```
GET /src/api/reports.php?action=pivot_table&date_from=2024-01-01&date_to=2024-01-31&rows=department&columns=status&values=count
```

#### Export Data
```
GET /src/api/reports.php?action=export_data&date_from=2024-01-01&date_to=2024-01-31&format=csv
```

### File Structure

```
public/
├── pages/
│   └── reports.php              # Main reports page
├── css/
│   └── reports.css              # Reports-specific styles
└── js/
    └── reports.js               # Reports functionality

src/
├── api/
│   └── reports.php              # Reports API endpoints
└── models/
    ├── Complaint.php            # Complaint data model
    ├── Transaction.php          # Transaction tracking
    ├── User.php                 # User management
    └── Customer.php             # Customer data
```

## Chart Types

### 1. Doughnut Chart (Status Distribution)
- Shows complaint status breakdown
- Interactive tooltips with percentages
- Color-coded for easy identification

### 2. Bar Chart (Priority Distribution)
- Displays complaints by priority level
- Ordered by priority importance (high → normal → low)
- Clean, minimal design

### 3. Pie Chart (Department Analysis)
- Shows complaints distribution across departments
- Legend with department names
- Percentage calculations

### 4. Horizontal Bar Chart (Category Analysis)
- Displays top complaint categories
- Horizontal orientation for better readability
- Limited to top 10 categories

### 5. Line Chart (Timeline)
- Shows complaints over time
- Multiple lines for different statuses
- Configurable grouping (daily/weekly/monthly)

### 6. Bar Chart (Monthly Trends)
- Compares total vs resolved complaints
- Monthly aggregation
- Side-by-side comparison

## Responsive Design

The reports system is fully responsive and works on all device sizes:

- **Desktop**: Full-featured interface with all charts visible
- **Tablet**: Optimized layout with stacked charts
- **Mobile**: Single-column layout with touch-friendly controls

### Mobile Optimizations
- Touch-friendly buttons and controls
- Swipe gestures for chart navigation
- Optimized font sizes and spacing
- Collapsible sections for better organization

## Export Capabilities

### CSV Export
- Complete complaint data export
- Includes all relevant fields
- Properly formatted for Excel/Google Sheets
- Date range filtering

### Chart Export
- Individual chart export as PNG
- High-resolution images
- Suitable for presentations and reports
- Automatic filename generation

### Print Support
- Print-optimized layouts
- Hidden controls and filters
- Clean, professional output
- Page break optimization

## Performance Optimizations

### Database Queries
- Optimized SQL queries with proper indexing
- Date range filtering to reduce data load
- Aggregated calculations at database level
- Efficient JOIN operations

### Frontend Performance
- Lazy loading of chart data
- Chart.js optimization
- Minimal DOM manipulation
- Efficient event handling

### Caching Strategy
- Browser-level caching for static assets
- API response caching for repeated requests
- Chart rendering optimization

## Security Features

### Access Control
- Role-based access (admin, controller, viewer)
- Session validation
- CSRF protection
- Input sanitization

### Data Protection
- SQL injection prevention
- XSS protection
- Secure API endpoints
- Audit logging

## Usage Instructions

### For Administrators
1. Navigate to the Reports page
2. Select date range for analysis
3. Choose report type (Dashboard/MIS/Performance/Pivot)
4. View interactive charts and tables
5. Export data as needed

### For Controllers
1. Access reports through the main menu
2. Filter by relevant date ranges
3. Focus on performance metrics
4. Monitor user activity
5. Track resolution times

### For Viewers
1. View read-only reports
2. Access dashboard overview
3. Monitor system performance
4. Export data for analysis

## Sample Data Generation

For testing purposes, the system includes a sample data generator:

```
GET /src/api/reports.php?action=generate_sample_data
```

This generates 50 sample complaints with realistic data for testing all report features.

## Customization Options

### Chart Colors
Colors can be customized in `reports.js`:
```javascript
const colors = ['#667eea', '#28a745', '#ffc107', '#dc3545', '#6c757d'];
```

### Date Ranges
Default date ranges can be modified:
```javascript
const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
```

### Chart Options
Chart.js options can be customized for each chart type in the JavaScript file.

## Troubleshooting

### Common Issues

1. **Charts Not Loading**
   - Check browser console for JavaScript errors
   - Verify Chart.js library is loaded
   - Ensure API endpoints are accessible

2. **Data Not Displaying**
   - Check database connectivity
   - Verify date range parameters
   - Review API response format

3. **Export Not Working**
   - Check file permissions
   - Verify CSV format compatibility
   - Review browser download settings

### Debug Mode
Enable debug mode by adding `?debug=1` to API URLs for detailed error information.

## Future Enhancements

### Planned Features
- Real-time data updates
- Advanced filtering options
- Custom report builder
- Email report scheduling
- Mobile app integration
- Advanced analytics (predictive modeling)

### Performance Improvements
- Server-side chart rendering
- Database query optimization
- CDN integration
- Progressive web app features

## Support

For technical support or feature requests, please contact the development team or refer to the main system documentation.

---

**Version**: 1.0  
**Last Updated**: January 2024  
**Compatibility**: PHP 7.4+, MySQL 5.7+, Modern Browsers
