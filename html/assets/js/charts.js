/**
 * Dashboard Charts Implementation
 * Uses Chart.js library to display analytics data
 */

$(document).ready(function() {
    // Chart instances
    let completionLineChart = null;
    let statusPieChart = null;
    let productivityBarChart = null;
    let priorityDoughnutChart = null;

    // Default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

    // Initialize date pickers
    $('#date-range-start').datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: today,
        defaultDate: thirtyDaysAgo,
        changeMonth: true,
        changeYear: true,
        onSelect: function(date) {
            $('#date-range-end').datepicker('option', 'minDate', date);
        }
    }).val(formatDate(thirtyDaysAgo));

    $('#date-range-end').datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: today,
        defaultDate: today,
        changeMonth: true,
        changeYear: true,
        onSelect: function(date) {
            $('#date-range-start').datepicker('option', 'maxDate', date);
        }
    }).val(formatDate(today));

    // Format date helper
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Initialize Task Completion Line Chart
    function initCompletionChart() {
        const ctx = document.getElementById('completion-line-chart').getContext('2d');
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        $.ajax({
            url: '/api/analytics.php',
            method: 'GET',
            data: {
                action: 'task_completion',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (completionLineChart) {
                        completionLineChart.destroy();
                    }

                    completionLineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: response.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            },
            error: function() {
                console.error('Failed to load completion chart data');
            }
        });
    }

    // Initialize Status Distribution Pie Chart
    function initStatusChart() {
        const ctx = document.getElementById('status-pie-chart').getContext('2d');
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        $.ajax({
            url: '/api/analytics.php',
            method: 'GET',
            data: {
                action: 'status_distribution',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (statusPieChart) {
                        statusPieChart.destroy();
                    }

                    statusPieChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: response.labels,
                            datasets: response.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            label += context.parsed + ' (' + percentage + '%)';
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            },
            error: function() {
                console.error('Failed to load status chart data');
            }
        });
    }

    // Initialize Team Productivity Bar Chart
    function initProductivityChart() {
        const ctx = document.getElementById('productivity-bar-chart').getContext('2d');
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        $.ajax({
            url: '/api/analytics.php',
            method: 'GET',
            data: {
                action: 'team_productivity',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (productivityBarChart) {
                        productivityBarChart.destroy();
                    }

                    productivityBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: response.labels,
                            datasets: response.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                }
                            },
                            scales: {
                                x: {
                                    stacked: false
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }
            },
            error: function() {
                console.error('Failed to load productivity chart data');
            }
        });
    }

    // Initialize Priority Breakdown Doughnut Chart
    function initPriorityChart() {
        const ctx = document.getElementById('priority-doughnut-chart').getContext('2d');
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        $.ajax({
            url: '/api/analytics.php',
            method: 'GET',
            data: {
                action: 'priority_breakdown',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (priorityDoughnutChart) {
                        priorityDoughnutChart.destroy();
                    }

                    priorityDoughnutChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: response.labels,
                            datasets: response.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            label += context.parsed + ' (' + percentage + '%)';
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            },
            error: function() {
                console.error('Failed to load priority chart data');
            }
        });
    }

    // Load completion rate statistics
    function loadCompletionStats() {
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        $.ajax({
            url: '/api/analytics.php',
            method: 'GET',
            data: {
                action: 'completion_rate',
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update stat cards with real data
                    $('#stat-tasks .stat-value').text(response.current.total);
                    $('#stat-completed .stat-value').text(response.current.completed);
                    $('#stat-progress .stat-value').text(response.current.in_progress);

                    // Update trend indicators
                    if (response.trend.direction === 'up') {
                        $('#stat-completed .stat-change').html(
                            '<i class="bi bi-arrow-up"></i> ' + response.trend.percentage.toFixed(1) + '% increase'
                        ).removeClass('text-warning text-danger').addClass('text-success');
                    } else if (response.trend.direction === 'down') {
                        $('#stat-completed .stat-change').html(
                            '<i class="bi bi-arrow-down"></i> ' + response.trend.percentage.toFixed(1) + '% decrease'
                        ).removeClass('text-success text-warning').addClass('text-danger');
                    } else {
                        $('#stat-completed .stat-change').html(
                            '<i class="bi bi-dash"></i> No change'
                        ).removeClass('text-success text-danger').addClass('text-warning');
                    }

                    // Update completion rate percentage
                    const completionBadge = response.current.completion_rate >= 70 ? 'success' :
                                          response.current.completion_rate >= 40 ? 'warning' : 'danger';

                    $('#stat-completed').find('.card-body').append(
                        '<div class="progress mt-2" style="height: 5px;">' +
                        '<div class="progress-bar bg-' + completionBadge + '" role="progressbar" ' +
                        'style="width: ' + response.current.completion_rate + '%;" ' +
                        'aria-valuenow="' + response.current.completion_rate + '" ' +
                        'aria-valuemin="0" aria-valuemax="100"></div></div>'
                    );
                }
            },
            error: function() {
                console.error('Failed to load completion statistics');
            }
        });
    }

    // Initialize all charts
    function initAllCharts() {
        initCompletionChart();
        initStatusChart();
        initProductivityChart();
        initPriorityChart();
        loadCompletionStats();
    }

    // Apply date range button click handler
    $('#apply-date-range').on('click', function() {
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        if (!startDate || !endDate) {
            showNotification('Please select both start and end dates', 'warning');
            return;
        }

        if (startDate > endDate) {
            showNotification('Start date must be before end date', 'warning');
            return;
        }

        // Show loading state
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Loading...');

        // Reload all charts with new date range
        initAllCharts();

        // Reset button state after a delay
        setTimeout(() => {
            $(this).prop('disabled', false).text('Apply');
            showNotification('Charts updated successfully', 'success');
        }, 1000);
    });

    // Quick date range selectors
    function addQuickDateSelectors() {
        const quickRanges = `
            <div class="btn-group btn-group-sm mb-2" role="group" id="quick-date-ranges">
                <button type="button" class="btn btn-outline-secondary" data-range="7">Last 7 days</button>
                <button type="button" class="btn btn-outline-secondary" data-range="30">Last 30 days</button>
                <button type="button" class="btn btn-outline-secondary" data-range="90">Last 90 days</button>
                <button type="button" class="btn btn-outline-secondary" data-range="365">Last year</button>
            </div>
        `;

        $('#date-range-card .card-body').prepend(quickRanges);

        // Handle quick range selection
        $('#quick-date-ranges button').on('click', function() {
            const days = parseInt($(this).data('range'));
            const endDate = new Date();
            const startDate = new Date(endDate);
            startDate.setDate(startDate.getDate() - days);

            $('#date-range-start').datepicker('setDate', startDate);
            $('#date-range-end').datepicker('setDate', endDate);

            // Mark selected button as active
            $('#quick-date-ranges button').removeClass('btn-primary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');

            // Apply the new range
            $('#apply-date-range').click();
        });

        // Set default to last 30 days
        $('#quick-date-ranges button[data-range="30"]').click();
    }

    // Export charts functionality
    function addExportButtons() {
        $('.card-header').each(function() {
            if ($(this).parent().find('canvas').length > 0) {
                $(this).append(
                    '<div class="float-end">' +
                    '<button class="btn btn-sm btn-outline-secondary export-chart" data-chart="' +
                    $(this).parent().find('canvas').attr('id') + '">' +
                    '<i class="bi bi-download"></i>' +
                    '</button>' +
                    '</div>'
                );
            }
        });

        // Handle export button clicks
        $(document).on('click', '.export-chart', function() {
            const chartId = $(this).data('chart');
            const canvas = document.getElementById(chartId);
            const url = canvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.download = chartId + '_' + new Date().getTime() + '.png';
            link.href = url;
            link.click();

            showNotification('Chart exported successfully', 'success');
        });
    }

    // Auto-refresh charts
    let autoRefreshInterval = null;

    function toggleAutoRefresh() {
        const refreshBtn = `
            <button class="btn btn-sm btn-outline-secondary ms-2" id="toggle-auto-refresh">
                <i class="bi bi-arrow-clockwise"></i> Auto-refresh: OFF
            </button>
        `;
        $('#date-range-card .col-md-8').append(refreshBtn);

        $('#toggle-auto-refresh').on('click', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
                $(this).html('<i class="bi bi-arrow-clockwise"></i> Auto-refresh: OFF');
                showNotification('Auto-refresh disabled', 'info');
            } else {
                autoRefreshInterval = setInterval(initAllCharts, 60000); // Refresh every minute
                $(this).html('<i class="bi bi-arrow-clockwise"></i> Auto-refresh: ON');
                showNotification('Auto-refresh enabled (1 minute interval)', 'info');
            }
        });
    }

    // Initialize everything
    addQuickDateSelectors();
    addExportButtons();
    toggleAutoRefresh();

    // Initial load of charts
    initAllCharts();

    // Handle window resize
    $(window).on('resize', function() {
        if (completionLineChart) completionLineChart.resize();
        if (statusPieChart) statusPieChart.resize();
        if (productivityBarChart) productivityBarChart.resize();
        if (priorityDoughnutChart) priorityDoughnutChart.resize();
    });
});