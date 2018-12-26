$(document).ready(function(){

    // Initial loading of the graphs.
    loadGraphs(false);
});

// Show graphs of delays and distances. The offset is determined by showZero.
function loadGraphs(showZero) {

    $.ajax({
        url: api_url,
        method: 'GET',
        success: function(data) {

            var timestamp = [];
            var distance = [];
            var delay = [];

            for (var i in data['trafficjams']) {
                timestamp.push(data['trafficjams'][i].timestamp.substring(11, 16));
                distance.push(data['trafficjams'][i].distance / 1000);
                delay.push(data['trafficjams'][i].delay / 60);
            }

            var chartdata = {
                labels: timestamp,
                datasets: [
                    {
                        label: 'Lengte file in km',
                        backgroundColor: 'rgba(200, 200, 200, 0.75)',
                        borderColor: 'rgba(200, 200, 200, 0.75)',
                        hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
                        hoverBorderColor: 'rgba(200, 200, 200, 1)',
                        data: distance
                    }
                ]
            };

            var ctx = $('#canvas');

            var barGraph = new Chart(ctx, {
                type: 'line',
                data: chartdata,
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: showZero
                            }
                        }]
                    }
                }
            });

            var chartdata2 = {
                labels: timestamp,
                datasets: [
                    {
                        label: 'Vertraging file in minuten',
                        backgroundColor: 'rgba(200, 200, 200, 0.75)',
                        borderColor: 'rgba(200, 200, 200, 0.75)',
                        hoverBackgroundColor: 'rgba(200, 200, 200, 1)',
                        hoverBorderColor: 'rgba(200, 200, 200, 1)',
                        data: delay
                    }
                ]
            };

            var ctx2 = $('#canvas2');

            var barGraph = new Chart(ctx2, {
                type: 'line',
                data: chartdata2,
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: showZero
                            }
                        }]
                    }
                }
            });
        },
        error: function(data) {
            console.log(data);
        }
    });
}
