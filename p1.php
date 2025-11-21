<?php
if(1==2)
{
/*
USE EventSchedulerDB;
GO
ALTER TABLE ScheduledEvents
ADD Notes VARBINARY(MAX); -- Use VARBINARY(MAX) to store long base64 strings
GO
*/
    
// (Same PHP connection and submission logic as before)
// Remember to fill in your database credentials:
$serverName = "your_server_name";
$database = "EventSchedulerDB";
$uid = "your_username";
$pwd = "your_password";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database = $database", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nextScheduleDate = $_POST['next_schedule_date'];
    $eventName = filter_input(INPUT_POST, 'event_name', FILTER_SANITIZE_STRING);
    $contactPerson = filter_input(INPUT_POST, 'contact_person', FILTER_SANITIZE_STRING);
    $contactNo = filter_input(INPUT_POST, 'contact_no', FILTER_SANITIZE_STRING);
    $scheduleType = $_POST['schedule_type'];
    $periodInterval = ($scheduleType == 'Periodic') ? $_POST['period_interval'] : 'None';
    // Notes field is now submitted as Base64 from JavaScript
    $notesBase64 = $_POST['notes_base64'];

    $tsql = "INSERT INTO ScheduledEvents (NextScheduleDate, EventName, ContactPerson, ContactNo, ScheduleType, PeriodInterval, Notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($tsql);
        $stmt->execute([$nextScheduleDate, $eventName, $contactPerson, $contactNo, $scheduleType, $periodInterval, $notesBase64]);
        $message = "New record created successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
}
// Get today's date in YYYY-MM-DD format for default value
$todayDate = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attractive Schedule Event Form</title>
    <style>
        /* (CSS styles from previous response remain the same for general layout) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h2 { color: #333; margin-bottom: 25px; text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px; transition: border-color 0.3s, box-shadow 0.3s; }
        input:focus, select:focus, textarea:focus { border-color: #007bff; box-shadow: 0 0 5px rgba(0, 123, 255, 0.3); outline: none; }
        .submit-btn { background-color: #007bff; color: white; padding: 14px 20px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 18px; transition: background-color 0.3s, transform 0.2s; }
        .submit-btn:hover { background-color: #0056b3; transform: translateY(-2px); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
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

        // Function to handle form submission and encode notes to Base64
        function handleSubmit() {
            const notesInput = document.getElementById('notes');
            const base64Input = document.getElementById('notes_base64');
            
            // Get the HTML content from the notes textarea
            const notesContent = notesInput.value; 
            
            // Encode the string to Base64
            // Note: window.btoa() is standard for Base64 encoding a string
            base64Input.value = btoa(unescape(encodeURIComponent(notesContent)));
            
            // The form will now submit the hidden input with the Base64 data
            return true; 
        }
    </script>
</head>
<body onload="toggleInterval()">
    <div class="form-container">
        <h2>Schedule Event Entry</h2>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return handleSubmit()">
            <div class="form-group">
                <label for="next_schedule_date">Next Schedule Date:</label>
                <!-- Default value set to today's date using PHP -->
                <input type="date" id="next_schedule_date" name="next_schedule_date" value="<?php echo $todayDate; ?>" required>
            </div>

            <div class="form-group">
                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" placeholder="e.g., Today's Note" required>
            </div>

            <div class="form-group">
                <label for="contact_person">Contact Person:</label>
                <input type="text" id="contact_person" name="contact_person" placeholder="Contact Name">
            </div>

            <div class="form-group">
                <label for="contact_no">Contact No:</label>
                <input type="text" id="contact_no" name="contact_no" placeholder="+1234567890">
            </div>

            <div class="form-group">
                <label for="schedule_type">Schedule Type:</label>
                <!-- Default value set to 'Once' for a simple note -->
                <select id="schedule_type" name="schedule_type" onchange="toggleInterval()">
                    <option value="Once" selected>Once (Today's Note)</option>
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
                <label for="notes">Notes (Long text, paste images here):</label>
                <textarea id="notes" name="notes" rows="6" placeholder="Enter detailed notes or paste images (images are saved as code)..."></textarea>
            </div>
            
            <!-- Hidden input to store the Base64 version of the notes -->
            <input type="hidden" id="notes_base64" name="notes_base64">

            <div class="form-group">
                <input type="submit" value="Save Schedule" class="submit-btn">
            </div>
        </form>
    </div>
</body>
</html>
