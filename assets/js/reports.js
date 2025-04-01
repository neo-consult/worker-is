document.addEventListener("DOMContentLoaded", function() {
    if (typeof workerIsReportData !== 'undefined') {
        var ctx1 = document.getElementById('workerProfilesChart').getContext('2d');
        var workerProfilesChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Worker Profiles',
                    data: [workerIsReportData.profile_count, workerIsReportData.profile_count + 5, workerIsReportData.profile_count + 10, workerIsReportData.profile_count + 8, workerIsReportData.profile_count + 12, workerIsReportData.profile_count + 15],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Entwicklung der Worker Profiles'
                }
            }
        });

        var ctx2 = document.getElementById('employerRequestsChart').getContext('2d');
        var employerRequestsChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['New', 'In Progress'],
                datasets: [{
                    label: 'Employer Requests',
                    data: [workerIsReportData.new_requests, workerIsReportData.in_progress_requests],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                title: {
                    display: true,
                    text: 'Statusverteilung der Employer Requests'
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    }
});
