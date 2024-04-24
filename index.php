<?php
// Connect to the SQLite database
try {
    $db = new PDO('sqlite:temperature.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Query the database for the last 24 hours of data
// -28 Hours due to UTC vs EST Conversion
$sql = "SELECT Temperature, DateTime FROM tempData WHERE DateTime >= strftime('%Y-%m-%dT%H:%M:%S','now','-28 hours')";
$stmt = $db->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extracting temperature and datetime arrays for chart data
$temperatures = array_column($data, 'Temperature');
$datetimes = array_map(function($datetime) {
	    return substr($datetime, 0, 19); // Remove decimal places and milliseconds
}, array_column($data, 'DateTime'));
?>

<html>
<head>
    <title>Last 24 Hours of Temperature Data</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Last 24 Hours of Temperature Data</h2>
    <canvas id="temperatureChart" width="800" height="400"></canvas>

    <script>
        // Using PHP to pass data to JavaScript
        var temperatures = <?php echo json_encode($temperatures); ?>;
        var datetimes = <?php echo json_encode($datetimes); ?>;

        // Chart.js configuration
        var ctx = document.getElementById('temperatureChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: datetimes,
                datasets: [{
                    label: 'Temperature (°F)',
                    data: temperatures,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
			ticks: {
				maxTicksLimit: 24
			}
                   },
		   y: {
                	scaleLabel: {
                        	display: true,
                        	labelString: 'Temperature (°F)'
                        }
		   }
		}
            }
        });
    </script>
</body>
</html>
