<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Help & User Manual - SAMPARK</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS (Animate On Scroll) CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Mermaid.js for diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/navbar.css" rel="stylesheet">
    <link href="../css/help.css" rel="stylesheet">
    
    <style>
        :root {
            /* Apple-inspired color palette */
            --apple-black: #000000;
            --apple-dark-gray: #666666;
            --apple-medium-gray: #979797;
            --apple-light-gray: #eeeeee;
            --apple-blue: #0088cc;
            
            /* Gradient colors */
            --gradient-start: hsla(330, 100%, 99%, 1);
            --gradient-end: hsla(0, 0%, 88%, 1);
        }
        
        /* Global Styles - Apple-inspired */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--gradient-start);
            background: linear-gradient(90deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            font-weight: 400;
            letter-spacing: -0.022em;
        }
        
        .main-content {
            flex: 1;
            padding-top: 20px;
            margin-top: 0;
        }
        
        .compact-header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1002;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .logo-left, .logo-right {
            flex-shrink: 0;
        }
        
        .railways-logo, .sampark-logo {
            height: 60px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .system-name-center {
            flex: 1;
            text-align: center;
        }
        
        .system-acronym {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--apple-black);
            margin: 0;
            letter-spacing: -0.022em;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.05;
        }
    </style>
</head>
<body>
    <!-- Compact Header -->
    <header class="compact-header">
        <div class="container">
            <div class="header-content">
                <!-- Left Logo -->
                <div class="logo-left">
                    <img src="../images/indian_railways_logo.png" alt="Indian Railways" class="railways-logo">
                </div>
                
                <!-- Center System Name -->
                <div class="system-name-center">
                    <h1 class="system-acronym">SAMPARK</h1>
                    <p class="system-full-name">Support and Mediation Portal for All Rail Cargo</p>
                </div>
                
                <!-- Right Logo -->
                <div class="logo-right">
                    <img src="../images/Icon SAMPARK.png" alt="SAMPARK" class="sampark-logo">
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container help-container my-5">

            <div class="help-header">
                <h1 class="display-5"><i class="fas fa-life-ring"></i> SAMPARK Help Center</h1>
                <p class="lead">A complete user manual for all roles in the system.</p>
            </div>

            <!-- Customer Role Section -->
            <section id="customer-guide" class="role-section">
                <h2 class="role-title">
                    <span class="role-badge role-customer">Customer</span> Guide
                </h2>
                
                <h4>Role Overview</h4>
                <p>As a Customer, you are the primary user of the portal. Your main functions are to submit new grievances, track their status, and provide feedback once they are resolved.</p>

                <h4>Customer Grievance Flow</h4>
                <div class="flowchart-container">
                    <div class="mermaid">
                    graph TD
                        A[Start] --> B{Submit New Grievance};
                        B --> C[Grievance ID Assigned];
                        C --> D{Track in My Grievances};
                        D --> E[Status: New];
                        E --> F[Status: In Progress];
                        F --> G{Action by Controller};
                        G --> H[Status: Resolved];
                        G --> I[Status: Reverted];
                        H --> J{Provide Feedback};
                        J --> K[Status: Closed];
                        I --> L{Provide More Info};
                        L --> F;
                    </div>
                </div>

                <div class="manual">
                    <h4>User Manual</h4>
                    <h5><i class="fas fa-home"></i> Home Page</h5>
                    <ul>
                        <li>View the latest <strong>News & Announcements</strong> from the railway authorities.</li>
                        <li>Access important services quickly through the <strong>Quick Links</strong> section.</li>
                    </ul>
                    <h5><i class="fas fa-plus-circle"></i> Submitting a New Grievance</h5>
                    <ul>
                        <li>Navigate to <strong>Grievances > New Grievance</strong>.</li>
                        <li>Select the <strong>Complaint Type</strong> and <strong>Subtype</strong>.</li>
                        <li>Provide a <strong>Detailed Description</strong> and other required fields.</li>
                        <li>Optionally, upload images as <strong>Evidence</strong>.</li>
                        <li>Click <strong>Submit Grievance</strong> to get a unique tracking ID.</li>
                    </ul>
                    <h5><i class="fas fa-list"></i> Tracking My Grievances</h5>
                    <ul>
                        <li>Go to <strong>Grievances > My Grievances</strong> to see all your submissions.</li>
                        <li>Click <strong>View</strong> to see the full details and history of a complaint.</li>
                    </ul>
                    <h5><i class="fas fa-star"></i> Providing Feedback (For 'Resolved' Grievances)</h5>
                    <ul>
                        <li>When a grievance status is <strong>Resolved</strong>, a "Give Feedback" button will appear.</li>
                        <li>Click it to rate the resolution and add comments. This closes the complaint.</li>
                    </ul>
                    <h5><i class="fas fa-reply"></i> Providing More Information (For 'Reverted' Grievances)</h5>
                    <ul>
                        <li>If a status is <strong>Reverted</strong>, a "Send Again" button will appear.</li>
                        <li>Click it to add more details or upload new evidence as requested.</li>
                    </ul>
                </div>
            </section>

            <!-- Controller Role Section -->
            <section id="controller-guide" class="role-section">
                <h2 class="role-title">
                    <span class="role-badge role-controller">Controller</span> Guide
                </h2>
                <h4>Role Overview</h4>
                <p>As a Controller, you manage and resolve grievances assigned to your department. Your goal is to investigate issues, take action, and communicate with the customer.</p>

                <h4>Complaint Handling Flow</h4>
                <div class="flowchart-container">
                    <div class="mermaid">
                    graph TD
                        A[New Complaint Assigned] --> B{View in Complaints to Me};
                        B --> C{Acknowledge};
                        C --> D[Status: In Progress];
                        D --> E{Investigate & Act};
                        E --> F[Resolve];
                        E --> G[Revert];
                        E --> H[Forward];
                        F --> I[Status: Resolved];
                        G --> J[Status: Reverted];
                        H --> K[Assigned to New Dept.];
                    </div>
                </div>

                <div class="manual">
                    <h4>User Manual</h4>
                    <h5><i class="fas fa-inbox"></i> Managing "Complaints to Me"</h5>
                    <ul>
                        <li>This is your main inbox for all assigned grievances.</li>
                        <li>Click <strong>View</strong> to open the detailed page for any complaint.</li>
                    </ul>
                    <h5><i class="fas fa-tasks"></i> Processing a Complaint</h5>
                    <ul>
                        <li><strong>Acknowledge:</strong> Mark a 'New' complaint as 'In Progress'.</li>
                        <li><strong>Resolve:</strong> After taking action, provide a resolution summary and mark as 'Resolved'.</li>
                        <li><strong>Revert:</strong> Send the complaint back to the customer if you need more information.</li>
                        <li><strong>Forward:</strong> Re-assign a complaint to the correct department if it was wrongly assigned to you.</li>
                    </ul>
                    <h5><i class="fas fa-chart-bar"></i> Accessing Reports</h5>
                    <ul>
                        <li>Navigate to the <strong>Reports</strong> section to view various system reports.</li>
                    </ul>
                </div>
            </section>
            
            <!-- Viewer Role Section -->
            <section id="viewer-guide" class="role-section">
                <h2 class="role-title">
                    <span class="role-badge role-viewer">Viewer</span> Guide
                </h2>
                <h4>Role Overview</h4>
                <p>The Viewer role is a read-only role designed for monitoring and oversight. Viewers can access system-wide reports but cannot take any action on complaints.</p>

                <h4>Viewer Flow</h4>
                <div class="flowchart-container">
                    <div class="mermaid">
                    graph TD
                        A[Login] --> B{Navigate to Reports};
                        B --> C{Select Report Type};
                        C --> D[View & Analyze Data];
                    </div>
                </div>

                <div class="manual">
                    <h4>User Manual</h4>
                    <h5><i class="fas fa-chart-pie"></i> Viewing Reports</h5>
                    <ul>
                        <li>Your primary function is to access the <strong>Reports</strong> section.</li>
                        <li>You can view all available reports, including complaint statistics and performance metrics.</li>
                        <li>Utilize the filters to narrow down the data to get the specific insights you need.</li>
                    </ul>
                </div>
            </section>

            <!-- Admin Role Section -->
            <section id="admin-guide" class="role-section">
                <h2 class="role-title">
                    <span class="role-badge role-admin">Admin</span> Guide
                </h2>
                <h4>Role Overview</h4>
                <p>As an Admin, you have the highest level of access. You are responsible for user management, system configuration, and overall monitoring.</p>

                <h4>Admin Functions Overview</h4>
                <div class="flowchart-container">
                    <div class="mermaid">
                    graph TD
                        A[Login] --> B{Admin Dashboard};
                        B --> C[Manage Users];
                        B --> D[System Settings];
                        B --> E[Communication];
                        B --> F[Monitoring];
                        C --> C1[Create, Edit, Deactivate];
                        D --> D1[Categories, Email Templates];
                        E --> E1[Bulk Email, News];
                        F --> F1[Logs, Reports, All Complaints];
                    </div>
                </div>

                <div class="manual">
                    <h4>User Manual</h4>
                    <h5><i class="fas fa-users-cog"></i> User Management</h5>
                    <ul>
                        <li>Go to <strong>Admin > Users</strong> to create, view, edit, and deactivate all user accounts.</li>
                    </ul>
                    <h5><i class="fas fa-cogs"></i> System Configuration</h5>
                    <ul>
                        <li><strong>Manage Categories:</strong> Define complaint types and subtypes.</li>
                        <li><strong>Email Templates:</strong> Create and edit automated emails.</li>
                        <li><strong>Quick Links:</strong> Manage the links on the Customer home page.</li>
                    </ul>
                    <h5><i class="fas fa-bullhorn"></i> Communication</h5>
                    <ul>
                        <li><strong>Bulk Email:</strong> Send mass emails to users, filtered by role or department.</li>
                        <li><strong>News & Announcements:</strong> Post updates for the customer home page.</li>
                    </ul>
                    <h5><i class="fas fa-shield-alt"></i> Monitoring & Oversight</h5>
                    <ul>
                        <li><strong>System Logs:</strong> View detailed logs of system activities for troubleshooting.</li>
                        <li><strong>Reports:</strong> Access all system reports with unrestricted filters.</li>
                    </ul>
                </div>
            </section>

        </div>
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Initialize Mermaid.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mermaid.initialize({ startOnLoad: true });
        });
    </script>
</body>
</html>