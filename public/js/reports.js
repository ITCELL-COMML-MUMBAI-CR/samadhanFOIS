/**
 * Reports JavaScript
 * Handles all reports functionality including charts, pivot tables, and data loading
 */

// Global variables
let charts = {};
let currentReportType = 'dashboard';
let currentDateFrom = '';
let currentDateTo = '';

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeReports();
});

function initializeReports() {
    // Set default dates - use current month
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    // If we're in the future (like 2025), use a reasonable past date range
    if (today.getFullYear() > 2024) {
        const pastDate = new Date(2024, 11, 1); // December 1, 2024
        const endDate = new Date(2024, 11, 31); // December 31, 2024
        document.getElementById('dateFrom').value = formatDate(pastDate);
        document.getElementById('dateTo').value = formatDate(endDate);
    } else {
        document.getElementById('dateFrom').value = formatDate(firstDayOfMonth);
        document.getElementById('dateTo').value = formatDate(today);
    }
    
    // Load initial dashboard report
    loadReports();
    
    // Add event listeners
    document.getElementById('reportType').addEventListener('change', function() {
        currentReportType = this.value;
        loadReports();
    });
    
    document.getElementById('timelineGroup').addEventListener('change', function() {
        loadTimelineChart();
    });
}

function loadReports() {
    showLoading(true);
    
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const reportType = document.getElementById('reportType').value;
    
    currentDateFrom = dateFrom;
    currentDateTo = dateTo;
    currentReportType = reportType;
    
    // Hide all report sections
    hideAllReportSections();
    
    switch (reportType) {
        case 'dashboard':
            loadDashboardReport(dateFrom, dateTo);
            break;
        case 'mis':
            loadMISReport(dateFrom, dateTo);
            break;
        case 'performance':
            loadPerformanceReport(dateFrom, dateTo);
            break;
        case 'pivot':
            loadPivotTable(dateFrom, dateTo);
            break;
    }
}

function loadDashboardReport(dateFrom, dateTo) {
    Promise.all([
        fetchDashboardStats(dateFrom, dateTo),
        fetchComplaintsByStatus(dateFrom, dateTo),
        fetchComplaintsByPriority(dateFrom, dateTo),
        fetchComplaintsByDepartment(dateFrom, dateTo),
        fetchComplaintsByCategory(dateFrom, dateTo),
        fetchComplaintsTimeline(dateFrom, dateTo)
    ]).then(([stats, statusData, priorityData, departmentData, categoryData, timelineData]) => {
        updateDashboardSummary(stats);
        createStatusChart(statusData);
        createPriorityChart(priorityData);
        createDepartmentChart(departmentData);
        createCategoryChart(categoryData);
        createTimelineChart(timelineData);
        
        document.getElementById('dashboardReport').style.display = 'block';
        showLoading(false);
    }).catch(error => {
        console.error('Error loading dashboard report:', error);
        showError('Failed to load dashboard report');
        showLoading(false);
    });
}

function loadMISReport(dateFrom, dateTo) {
    fetchMISReport(dateFrom, dateTo).then(data => {
        updateMISSummary(data.executive_summary);
        updateDepartmentTable(data.department_performance);
        createMonthlyTrendsChart(data.monthly_trends);
        
        document.getElementById('misReport').style.display = 'block';
        showLoading(false);
    }).catch(error => {
        console.error('Error loading MIS report:', error);
        showError('Failed to load MIS report');
        showLoading(false);
    });
}

function loadPerformanceReport(dateFrom, dateTo) {
    Promise.all([
        fetchPerformanceMetrics(dateFrom, dateTo),
        fetchUserActivity(dateFrom, dateTo)
    ]).then(([metrics, userActivity]) => {
        updatePerformanceMetrics(metrics);
        updateUserActivityTable(userActivity);
        
        document.getElementById('performanceReport').style.display = 'block';
        showLoading(false);
    }).catch(error => {
        console.error('Error loading performance report:', error);
        showError('Failed to load performance report');
        showLoading(false);
    });
}

function loadPivotTable(dateFrom, dateTo) {
    const rows = document.getElementById('pivotRows').value;
    const columns = document.getElementById('pivotColumns').value;
    const values = document.getElementById('pivotValues').value;
    
    fetchPivotTable(dateFrom, dateTo, rows, columns, values).then(data => {
        updatePivotTable(data);
        document.getElementById('pivotReport').style.display = 'block';
        showLoading(false);
    }).catch(error => {
        console.error('Error loading pivot table:', error);
        showError('Failed to load pivot table');
        showLoading(false);
    });
}

// API Functions
async function fetchDashboardStats(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=dashboard_stats&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchComplaintsByStatus(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=complaints_by_status&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchComplaintsByPriority(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=complaints_by_priority&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchComplaintsByDepartment(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=complaints_by_department&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchComplaintsByCategory(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=complaints_by_category&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchComplaintsTimeline(dateFrom, dateTo) {
    const groupBy = document.getElementById('timelineGroup').value;
    const response = await fetch(`../src/api/reports.php?action=complaints_timeline&date_from=${dateFrom}&date_to=${dateTo}&group_by=${groupBy}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchMISReport(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=mis_report&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchPerformanceMetrics(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=performance_metrics&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchUserActivity(dateFrom, dateTo) {
    const response = await fetch(`../src/api/reports.php?action=user_activity&date_from=${dateFrom}&date_to=${dateTo}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

async function fetchPivotTable(dateFrom, dateTo, rows, columns, values) {
    const response = await fetch(`../src/api/reports.php?action=pivot_table&date_from=${dateFrom}&date_to=${dateTo}&rows=${rows}&columns=${columns}&values=${values}`);
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    return data.data;
}

// Chart Creation Functions
function createStatusChart(data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    if (charts.statusChart) {
        charts.statusChart.destroy();
    }
    
    const colors = ['#667eea', '#28a745', '#ffc107', '#dc3545', '#6c757d'];
    
    charts.statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.status),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const percentage = context.parsed / context.dataset.data.reduce((a, b) => a + b, 0) * 100;
                            return `${label}: ${value} (${percentage.toFixed(1)}%)`;
                        }
                    }
                }
            }
        }
    });
}

function createPriorityChart(data) {
    const ctx = document.getElementById('priorityChart').getContext('2d');
    
    if (charts.priorityChart) {
        charts.priorityChart.destroy();
    }
    
    const colors = ['#dc3545', '#ffc107', '#28a745'];
    
    charts.priorityChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.priority),
            datasets: [{
                label: 'Number of Complaints',
                data: data.map(item => item.count),
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function createDepartmentChart(data) {
    const ctx = document.getElementById('departmentChart').getContext('2d');
    
    if (charts.departmentChart) {
        charts.departmentChart.destroy();
    }
    
    charts.departmentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(item => item.department),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: generateColors(data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

function createCategoryChart(data) {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    if (charts.categoryChart) {
        charts.categoryChart.destroy();
    }
    
    charts.categoryChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.map(item => item.category),
            datasets: [{
                label: 'Number of Complaints',
                data: data.map(item => item.count),
                backgroundColor: generateColors(data.length),
                borderColor: generateColors(data.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function createTimelineChart(data) {
    const ctx = document.getElementById('timelineChart').getContext('2d');
    
    if (charts.timelineChart) {
        charts.timelineChart.destroy();
    }
    
    charts.timelineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.period),
            datasets: [
                {
                    label: 'Total Complaints',
                    data: data.map(item => item.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true
                },
                {
                    label: 'Resolved',
                    data: data.map(item => item.resolved),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true
                },
                {
                    label: 'Pending',
                    data: data.map(item => item.pending),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

function createMonthlyTrendsChart(data) {
    const ctx = document.getElementById('monthlyTrendsChart').getContext('2d');
    
    if (charts.monthlyTrendsChart) {
        charts.monthlyTrendsChart.destroy();
    }
    
    charts.monthlyTrendsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.month),
            datasets: [
                {
                    label: 'Total Complaints',
                    data: data.map(item => item.complaints),
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 1
                },
                {
                    label: 'Resolved',
                    data: data.map(item => item.resolved),
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
}

// Update Functions
function updateDashboardSummary(stats) {
    document.getElementById('totalComplaints').textContent = stats.summary.total_complaints;
    document.getElementById('resolvedComplaints').textContent = stats.summary.resolved_complaints;
    document.getElementById('pendingComplaints').textContent = stats.summary.pending_complaints;
    
    const resolutionRate = stats.summary.total_complaints > 0 
        ? ((stats.summary.resolved_complaints / stats.summary.total_complaints) * 100).toFixed(1)
        : '0';
    document.getElementById('resolutionRate').textContent = resolutionRate + '%';
}

function updateMISSummary(summary) {
    const misSummary = document.getElementById('misSummary');
    misSummary.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-primary">${summary.total_complaints}</h4>
                    <p class="text-muted">Total Complaints</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-success">${summary.resolved}</h4>
                    <p class="text-muted">Resolved</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-warning">${summary.pending}</h4>
                    <p class="text-muted">Pending</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <h4 class="text-info">${summary.resolution_rate}%</h4>
                    <p class="text-muted">Resolution Rate</p>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info">
                    <strong>Average Resolution Time:</strong> ${summary.avg_resolution_days ? summary.avg_resolution_days.toFixed(1) : 'N/A'} days
                </div>
            </div>
        </div>
    `;
}

function updateDepartmentTable(data) {
    const tbody = document.querySelector('#departmentTable tbody');
    tbody.innerHTML = '';
    
    data.forEach(dept => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${dept.department}</strong></td>
            <td>${dept.total}</td>
            <td>${dept.resolved}</td>
            <td>
                <div class="progress">
                    <div class="progress-bar" style="width: ${dept.resolution_rate}%"></div>
                </div>
                <small>${dept.resolution_rate}%</small>
            </td>
            <td>${dept.avg_days ? dept.avg_days.toFixed(1) : 'N/A'}</td>
        `;
        tbody.appendChild(row);
    });
}

function updatePerformanceMetrics(metrics) {
    const container = document.getElementById('performanceMetrics');
    container.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Resolution Time</h5>
                        <h3 class="text-primary">${metrics.resolution_time.avg_resolution_days ? metrics.resolution_time.avg_resolution_days.toFixed(1) : 'N/A'} days</h3>
                        <p class="text-muted">Average</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Response Time</h5>
                        <h3 class="text-success">${metrics.response_time.avg_response_hours ? metrics.response_time.avg_response_hours.toFixed(1) : 'N/A'} hours</h3>
                        <p class="text-muted">Average</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Satisfaction Rate</h5>
                        <h3 class="text-info">${metrics.satisfaction.resolution_rate}%</h3>
                        <p class="text-muted">Resolution Rate</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function updateUserActivityTable(data) {
    const tbody = document.querySelector('#userActivityTable tbody');
    tbody.innerHTML = '';
    
    data.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.user_name}</td>
            <td>${user.department}</td>
            <td><span class="badge bg-primary">${user.total_actions}</span></td>
            <td>${user.forwards}</td>
            <td>${user.status_updates}</td>
            <td>${user.assignments}</td>
        `;
        tbody.appendChild(row);
    });
}

function updatePivotTable(data) {
    const table = document.getElementById('pivotTable');
    const thead = table.querySelector('thead tr');
    const tbody = table.querySelector('tbody');
    
    // Clear existing content
    thead.innerHTML = '<th>Category</th>';
    tbody.innerHTML = '';
    
    // Add column headers
    data.columns.forEach(column => {
        const th = document.createElement('th');
        th.textContent = column;
        thead.appendChild(th);
    });
    
    // Add data rows
    data.rows.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><strong>${row}</strong></td>`;
        
        data.columns.forEach(column => {
            const td = document.createElement('td');
            td.textContent = data.data[row] && data.data[row][column] ? data.data[row][column] : '0';
            tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
}

// Utility Functions
function showLoading(show) {
    const loadingIndicator = document.getElementById('loadingIndicator');
    loadingIndicator.style.display = show ? 'block' : 'none';
}

function hideAllReportSections() {
    const sections = ['dashboardReport', 'misReport', 'performanceReport', 'pivotReport'];
    sections.forEach(section => {
        document.getElementById(section).style.display = 'none';
    });
}

function showError(message) {
    // Create and show error alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function generateColors(count) {
    const colors = [
        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
        '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140'
    ];
    
    const result = [];
    for (let i = 0; i < count; i++) {
        result.push(colors[i % colors.length]);
    }
    return result;
}

// Export and Print Functions
function exportReport() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const format = 'csv';
    
    window.open(`../src/api/reports.php?action=export_data&date_from=${dateFrom}&date_to=${dateTo}&format=${format}`, '_blank');
}

function printReport() {
    window.print();
}

function exportChart(chartId) {
    const chart = charts[chartId];
    if (chart) {
        const link = document.createElement('a');
        link.download = `${chartId}_${currentDateFrom}_${currentDateTo}.png`;
        link.href = chart.toBase64Image();
        link.click();
    }
}

// Timeline chart reload function
function loadTimelineChart() {
    if (currentReportType === 'dashboard') {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        fetchComplaintsTimeline(dateFrom, dateTo).then(data => {
            createTimelineChart(data);
        }).catch(error => {
            console.error('Error loading timeline chart:', error);
        });
    }
}

// Pivot table reload function
function loadPivotTable() {
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    loadPivotTable(dateFrom, dateTo);
}
