<?php

/*

CREATE DATABASE EventSchedulerDB;
GO
USE EventSchedulerDB;
GO
CREATE TABLE ScheduledEvents (
    EventID INT PRIMARY KEY IDENTITY(1,1),
    NextScheduleDate DATE NOT NULL,
    EventName VARCHAR(255) NOT NULL,
    ContactPerson VARCHAR(100),
    ContactNo VARCHAR(20),
    ScheduleType VARCHAR(10) NOT NULL,
    PeriodInterval VARCHAR(50) -- e.g., 'Yearly', 'Every 6 Months', 'None'
);
GO


*/
// Database connection details for SQL Server
$serverName = "your_server_name"; // e.g., "(local)", "localhost", or IP address
$database = "EventSchedulerDB";
$uid = "your_username";
$pwd = "your_password";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database = $database", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true); // for direct query execution
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $nextScheduleDate = $_POST['next_schedule_date'];
    $eventName = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
    $contactPerson = filter_input(INPUT_POST, 'contact_person', FILTER_SANITIZE_STRING);
    $contactNo = filter_input(INPUT_POST, 'contact_no', FILTER_SANITIZE_STRING);
    $scheduleType = $_POST['schedule_type'];
    $periodInterval = ($scheduleType == 'Periodic') ? $_POST['period_interval'] : 'None';

    // Insert data into the database
    $tsql = "INSERT INTO ScheduledEvents (NextScheduleDate, EventName, ContactPerson, ContactNo, ScheduleType, PeriodInterval) VALUES (?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($tsql);
        $stmt->execute([$nextScheduleDate, $eventName, $contactPerson, $contactNo, $scheduleType, $periodInterval]);
        $message = "New record created successfully";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Event Form</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 300px; padding: 8px; }
        .message { padding: 10px; margin-bottom: 15px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
    <script>
        // JavaScript to toggle visibility of the interval selection based on schedule type
        function toggleInterval() {
            var scheduleType = document.getElementById("schedule_type").value;
            var intervalGroup = document.getElementById("interval_group");
            if (scheduleType === "Periodic") {
                intervalGroup.style.display = "block";
            } else {
                intervalGroup.style.display = "none";
            }
        }
    </script>
</head>
<body onload="toggleInterval()">
    <h2>Schedule Event Entry</h2>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="form-group">
            <label for="next_schedule_date">Next Schedule Date:</label>
            <input type="date" id="next_schedule_date" name="next_schedule_date" required>
        </div>

        <div class="form-group">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" required>
        </div>

        <div class="form-group">
            <label for="contact_person">Contact Person:</label>
            <input type="text" id="contact_person" name="contact_person">
        </div>

        <div class="form-group">
            <label for="contact_no">Contact No:</label>
            <input type="text" id="contact_no" name="contact_no">
        </div>

        <div class="form-group">
            <label for="schedule_type">Schedule Type:</label>
            <select id="schedule_type" name="schedule_type" onchange="toggleInterval()">
                <option value="Once">Once</option>
                <option value="Periodic">Periodic</option>
            </select>
        </div>

        <div class="form-group" id="interval_group" style="display: none;">
            <label for="period_interval">Period Interval:</label>
            <select id="period_interval" name="period_interval">
                <option value="Yearly">Yearly (for birthdays)</option>
                <option value="Every 6 Months">Every 6 Months (for PUC/premium pay)</option>
            </select>
        </div>

        <div class="form-group">
            <input type="submit" value="Save Schedule">
        </div>
    </form>
</body>
</html>
