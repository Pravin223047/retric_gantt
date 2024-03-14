<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "retric_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$echo = "";
$dataArray = [];

if (isset($_POST['submit'])) {
    $task_name = $_POST['task_name'];
    $resource = isset($_POST['resource']) ? $_POST['resource'] : "";
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $duration = round((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24));
    $percent_complete = $_POST['percent'];
    $dependency = $_POST['dependency'];
    $sql = "INSERT INTO retric_data (task_name, resource, start_date, end_date, duration, percent_complete, dependencies) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $task_name, $resource, $start_date, $end_date, $duration, $percent_complete, $dependency);

    if ($stmt->execute()) {
        $echo1 = "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $stmt->close();
}
$sql = "SELECT task_name, resource, start_date, end_date, duration, percent_complete, dependencies FROM retric_data";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $start_date = date('Y-m-d', strtotime($row['start_date']));
        $end_date = date('Y-m-d', strtotime($row['end_date']));
        $dataArray[] = [
            $row['task_name'],
            $row['task_name'],
            $row['resource'],
            $start_date,
            $end_date,
            (int) $row['duration'],
            (int) $row['percent_complete'],
            $row['dependencies']
        ];
    }
} else {
    $echo = "0";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Gantt Chart</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>Dynamic Gantt Chart</h1>
        <div class="box">
            <div class="right_box">
                <header>Input Your Data Here</header>
                <form action="#" class="form" method="post">
                    <div class="input-box">
                        <label for="task_name">Task Name</label>
                        <input type="text" name="task_name" placeholder="Enter task name" required>
                    </div>
                    <div class="column">
                        <div class="input-box">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="input-box">
                            <label for="end_date">End date</label>
                            <input type="date" name="end_date" required>
                        </div>
                    </div>
                    <div class="column">
                        <div class="input-box">
                            <label for="percent">Percent Completion</label>
                            <input type="text" name="percent" placeholder="Enter percent completion" required>
                        </div>
                        <div class="input-box">
                            <label for="duration">Duration</label>
                            <input type="number" name="duration"
                                placeholder="<?php echo isset($duration) ? $duration : ''; ?>" disabled>
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="resource">Resource</label>
                        <input type="text" name="resource" placeholder="Enter resource" required>
                    </div>
                    <div class="input-box dependency">
                        <label for="dependency">Dependency</label>
                        <input type="text" name="dependency" placeholder="Enter dependency" id="dependency" required>
                    </div>
                    <button type="submit" name="submit">Add Task</button>
                </form>
            </div>
            <div class="arrow">→</div>
            <div class="left_box">
                <div id="chart_div"></div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['gantt']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Task ID');
            data.addColumn('string', 'Task Name');
            data.addColumn('string', 'Resource');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            var dataJSON = <?php echo json_encode($dataArray); ?>;
            if (Array.isArray(dataJSON) && dataJSON.length > 0) {
                dataJSON.forEach(function (row) {
                    row[3] = new Date(row[3]);
                    row[4] = new Date(row[4]);
                });
                data.addRows(dataJSON);
            } else {
                console.error('No data fetched from PHP or data is not in the expected format:', dataJSON);
            }
            var options = {
                height: 350,
            };
            var chart = new google.visualization.Gantt(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>
</body>

</html>