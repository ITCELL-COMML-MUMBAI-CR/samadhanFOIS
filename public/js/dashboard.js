/**
 * Dashboard JavaScript
 * Handles all dashboard functionality including data loading, charts, and interactions
 */

let categoryChart = null;
let typeChart = null;
let currentTimeline = 'current';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    loadDashboardData(currentTimeline);
    
    // Timeline toggle event listeners
    document.querySelectorAll('input[name="timeline"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentTimeline = this.value;
            loadDashboardData(currentTimeline);
        });
    });
    
    // Status bifurcation card is now display-only, no click event needed
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        if (!document.hidden) {
            loadDashboardData(currentTimeline);
        }
    }, 300000);
});

/**
 * Load dashboard data from API
 */
function loadDashboardData(timeline) {
    showLoadingState();
    
    fetch(`${BASE_URL}api/dashboard?timeline=${timeline}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateDashboard(data.data);
            } else {
                showError('Failed to load dashboard data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load dashboard data. Please check your connection and try again.');
        });
}

/**
 * Update dashboard with new data
 */
function updateDashboard(data) {
    try {
        // Update first row metrics
        updateFirstRow(data.current.firstRow, data.variance.firstRow);
        
        // Update second row charts
        updateSecondRow(data.current.secondRow);
        
        // Update third row
        updateThirdRow(data.current.thirdRow, data.variance.thirdRow);
        
        hideLoadingState();
    } catch (error) {
        console.error('Error updating dashboard:', error);
        showError('Error updating dashboard display');
    }
}

/**
 * Update first row metrics
 */
function updateFirstRow(data, variance) {
    const elements = {
        totalComplaints: document.getElementById('totalComplaints'),
        averagePendency: document.getElementById('averagePendency'),
        averageReplyTime: document.getElementById('averageReplyTime'),
        numberOfForwards: document.getElementById('numberOfForwards')
    };
    
    // Update values
    elements.totalComplaints.textContent = formatNumber(data.totalComplaints);
    elements.averagePendency.textContent = formatNumber(data.averagePendency, 1);
    elements.averageReplyTime.textContent = formatNumber(data.averageReplyTime, 1);
    elements.numberOfForwards.textContent = formatNumber(data.numberOfForwards);
    
    // Update status bifurcation display
    updateStatusBifurcation(data.statusBifurcation);
    
    // Update variance indicators with consistent logic for all metrics
    updateVarianceIndicator('totalComplaintsVariance', variance.totalComplaints);
    updateVarianceIndicator('averagePendencyVariance', variance.averagePendency);
    updateVarianceIndicator('averageReplyTimeVariance', variance.averageReplyTime);
    updateVarianceIndicator('numberOfForwardsVariance', variance.numberOfForwards);
}

/**
 * Update second row charts
 */
function updateSecondRow(data) {
    updateCategoryChart(data.categoryWiseCount);
    updateTypeChart(data.typeWiseCount);
}

/**
 * Update third row customer analytics
 */
function updateThirdRow(data, variance) {
    const customersAddedElement = document.getElementById('customersAdded');
    if (customersAddedElement) {
        customersAddedElement.textContent = formatNumber(data.customersAdded);
    }
    updateVarianceIndicator('customersAddedVariance', variance.customersAdded);
}

/**
 * Update category chart
 */
function updateCategoryChart(categoryData) {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;
    
    if (categoryChart) {
        categoryChart.destroy();
    }
    
    const labels = categoryData.map(item => item.category || 'Unknown');
    const values = categoryData.map(item => item.count);
    const colors = generateColors(labels.length);
    
    categoryChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
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
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Update type chart
 */
function updateTypeChart(typeData) {
    const ctx = document.getElementById('typeChart');
    if (!ctx) return;
    
    if (typeChart) {
        typeChart.destroy();
    }
    
    const labels = typeData.map(item => item.Type || 'Unknown');
    const values = typeData.map(item => item.count);
    const colors = generateColors(labels.length);
    
    typeChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Count',
                data: values,
                backgroundColor: colors,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            return 'Click for subtype details';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const type = labels[index];
                    showSubtypeBifurcation(type);
                }
            }
        }
    });
}

/**
 * Show subtype bifurcation modal
 */
function showSubtypeBifurcation(type) {
    showLoadingModal('Loading subtype data...');
    
    fetch(`${BASE_URL}api/subtype_bifurcation?type=${encodeURIComponent(type)}&timeline=${currentTimeline}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();
            if (data.success) {
                showSubtypeModal(type, data.data);
            } else {
                showError('Failed to load subtype data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showError('Failed to load subtype data');
        });
}

/**
 * Show subtype modal with data
 */
function showSubtypeModal(type, subtypeData) {
    let modalContent = `<h6>Subtype Bifurcation for: ${type}</h6>`;
    modalContent += '<div class="table-responsive"><table class="table table-sm">';
    modalContent += '<thead><tr><th>Subtype</th><th>Count</th><th>Percentage</th></tr></thead><tbody>';
    
    const total = subtypeData.reduce((sum, item) => sum + item.count, 0);
    
    subtypeData.forEach(item => {
        const percentage = total > 0 ? ((item.count / total) * 100).toFixed(1) : '0.0';
        modalContent += `<tr><td>${item.Subtype || 'Unknown'}</td><td>${item.count}</td><td>${percentage}%</td></tr>`;
    });
    
    modalContent += '</tbody></table></div>';
    
    showModal(`Subtype Bifurcation - ${type}`, modalContent);
}

/**
 * Show status bifurcation modal
 */
function showStatusBifurcationModal() {
    showLoadingModal('Loading status data...');
    
    fetch(`${BASE_URL}api/dashboard?timeline=${currentTimeline}`)
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();
            if (data.success) {
                const statusData = data.data.current.firstRow.statusBifurcation;
                let modalContent = '<h6>Status Distribution</h6>';
                modalContent += '<div class="table-responsive"><table class="table table-sm">';
                modalContent += '<thead><tr><th>Status</th><th>Count</th><th>Percentage</th></tr></thead><tbody>';
                
                const total = statusData.reduce((sum, item) => sum + item.count, 0);
                
                statusData.forEach(item => {
                    const percentage = total > 0 ? ((item.count / total) * 100).toFixed(1) : '0.0';
                    const statusClass = getStatusClass(item.status);
                    modalContent += `<tr><td><span class="status-badge ${statusClass}">${item.status || 'Unknown'}</span></td><td>${item.count}</td><td>${percentage}%</td></tr>`;
                });
                
                modalContent += '</tbody></table></div>';
                
                showModal('Status Bifurcation Details', modalContent);
            } else {
                showError('Failed to load status data');
            }
        })
        .catch(error => {
            hideLoadingModal();
            console.error('Error:', error);
            showError('Failed to load status data');
        });
}

/**
 * Update status bifurcation display
 */
function updateStatusBifurcation(statusData) {
    const container = document.getElementById('statusBifurcationDetails');
    if (!container) return;
    
    if (!statusData || statusData.length === 0) {
        container.innerHTML = '<div class="text-center text-muted">No data available</div>';
        return;
    }
    
    let html = '<div class="status-breakdown">';
    
    statusData.forEach(item => {
        const statusClass = getStatusClass(item.status);
        const statusLabel = item.status || 'Unknown';
        const count = item.count || 0;
        
        html += `
            <div class="status-item ${statusClass}">
                <span class="status-count">${count}</span>
                <span class="status-label">${statusLabel}</span>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

/**
 * Update variance indicator with consistent logic for all metrics
 */
function updateVarianceIndicator(elementId, variance) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // For all metrics: increase is red, decrease is green
    if (variance > 0) {
        // Increase: Red color with upward arrow and + sign
        element.innerHTML = `<i class="fas fa-arrow-up text-danger"></i> +${variance}%`;
        element.className = 'text-danger variance-indicator';
    } else if (variance < 0) {
        // Decrease: Green color with downward arrow and - sign
        element.innerHTML = `<i class="fas fa-arrow-down text-success"></i> ${variance}%`;
        element.className = 'text-success variance-indicator';
    } else {
        // No change: Muted color with minus sign
        element.innerHTML = '<i class="fas fa-minus text-muted"></i> 0%';
        element.className = 'text-muted variance-indicator';
    }
}

/**
 * Generate colors for charts
 */
function generateColors(count) {
    const colors = [
        '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
        '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
    ];
    
    const result = [];
    for (let i = 0; i < count; i++) {
        result.push(colors[i % colors.length]);
    }
    return result;
}

/**
 * Format number with optional decimal places
 */
function formatNumber(num, decimals = 0) {
    if (num === null || num === undefined) return '-';
    if (decimals > 0) {
        return Number(num).toFixed(decimals);
    }
    return Number(num).toLocaleString();
}

/**
 * Get status class for styling
 */
function getStatusClass(status) {
    const statusMap = {
        'pending': 'status-pending',
        'Pending': 'status-pending',
        'replied': 'status-replied',
        'Replied': 'status-replied',
        'closed': 'status-closed',
        'Closed': 'status-closed',
        'reverted': 'status-reverted',
        'Reverted': 'status-reverted'
    };
    return statusMap[status] || 'status-pending';
}

/**
 * Show loading state
 */
function showLoadingState() {
    document.querySelectorAll('.dashboard-card h3').forEach(el => {
        el.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    });
    
    // Also show loading for status bifurcation
    const statusContainer = document.getElementById('statusBifurcationDetails');
    if (statusContainer) {
        statusContainer.innerHTML = '<div class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    }
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    // Loading state will be cleared when data is updated
}

/**
 * Show modal with content
 */
function showModal(title, content) {
    const modal = new bootstrap.Modal(document.getElementById('statusBifurcationModal'));
    document.getElementById('statusBifurcationModalLabel').textContent = title;
    document.getElementById('statusBifurcationDetails').innerHTML = content;
    modal.show();
}

/**
 * Show loading modal
 */
function showLoadingModal(message = 'Loading...') {
    const modal = new bootstrap.Modal(document.getElementById('statusBifurcationModal'));
    document.getElementById('statusBifurcationModalLabel').textContent = 'Loading';
    document.getElementById('statusBifurcationDetails').innerHTML = `
        <div class="text-center py-4">
            <div class="loading-spinner mb-3"></div>
            <p class="text-muted">${message}</p>
        </div>
    `;
    modal.show();
}

/**
 * Hide loading modal
 */
function hideLoadingModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('statusBifurcationModal'));
    if (modal) {
        modal.hide();
    }
}

/**
 * Show error message
 */
function showError(message) {
    console.error(message);
    hideLoadingState();
    
    // Show error toast or notification
    if (typeof showToast === 'function') {
        showToast('error', message);
    } else {
        alert(message);
    }
}

// Export functions for global access
window.Dashboard = {
    loadDashboardData,
    showSubtypeBifurcation,
    showStatusBifurcationModal
};
