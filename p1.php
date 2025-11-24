<?php
if(1==1){
// (Same PHP connection and submission logic as before)
// Remember to fill in your database credentials:
$serverName = "localhost:3306";
$database = "vpsmcowin9_app";
$uid = "vpsm_app";
$pwd = "Shailesh@1984";

    /*
try {
    $conn = new PDO("sqlsrv:server=$serverName;Database = $database", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
} catch (PDOException $e) {
    die("Error connecting to SQL Server: " . $e->getMessage());
}
*/
try {
    // MySQL PDO connection string
    $conn = new PDO("mysql:host=$serverName;dbname=$database;charset=utf8mb4", $uid, $pwd);
    
    // Set PDO attributes for error handling and character set
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Setting character set via DSN is preferred for MySQL
    // $conn->exec("set names utf8mb4"); // Alternative if DSN parameter isn't used

    $tableName = "ScheduledEvents";
     // 1. Check if the table exists
    $checkTable = $conn->query("SHOW TABLES LIKE '$tableName'");
    
    if ($checkTable->rowCount() == 0) {
        // Table does not exist, create it
        $createTableSQL = "
        CREATE TABLE $tableName (
            EventID INT AUTO_INCREMENT PRIMARY KEY,
            NextScheduleDate DATE NOT NULL,
            EventName VARCHAR(255) NOT NULL,
            ContactPerson VARCHAR(100),
            ContactNo VARCHAR(100),
            ScheduleType VARCHAR(20) NOT NULL,
            PeriodInterval VARCHAR(50), -- e.g., 'Yearly', 'Every 6 Months', 'None'
            Notes LONGTEXT -- LONGTEXT is suitable for storing Base64 encoded HTML
        );";
        
        $conn->exec($createTableSQL);
        // Optional: you can log a message that the table was created
        // echo "<script>console.log('Table $tableName created successfully.');</script>";
    } 
} catch (PDOException $e) {
    die("Error connecting to MySQL: " . $e->getMessage());
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

        
        // --- Telegram Bot Configuration ---
        $telegramBotToken = "7672249611:AAHOPbCKIOxsUW0G1HmRpMtOq_cS_AVmvP0"; // Replace with your actual token
        $telegramChatId = "1635904266";   
        sendTelegramMessage($telegramBotToken, $telegramChatId, $message); // $notificationText);
        
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}
}
// Get today's date in YYYY-MM-DD format for default value
$todayDate = date('Y-m-d');

// Function to send a message to Telegram
function sendTelegramMessage($token, $chatId, $messageText) {
    $website = "https://api.telegram.org/bot" . $token;
    $params = [
        'chat_id' => $chatId,
        'text' => $messageText,
        'parse_mode' => 'HTML' // Optional: Allows HTML formatting in your message
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $website . '/sendMessage');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // May be needed depending on your server
    $result = curl_exec($ch);
    curl_close($ch);
    // Optional: You can add error handling here by checking $result
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Event Form with Editor</title>
    <!-- Include Quill CSS from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        /* (CSS styles from previous response remain the same for general layout) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .form-container { background-color: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); width: 100%; max-width: 600px; }
        h2 { color: #333; margin-bottom: 25px; text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #555; font-weight: bold; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px; transition: border-color 0.3s, box-shadow 0.3s; }
        input:focus, select:focus { border-color: #007bff; box-shadow: 0 0 5px rgba(0, 123, 255, 0.3); outline: none; }
        .submit-btn { background-color: #007bff; color: white; padding: 14px 20px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 18px; transition: background-color 0.3s, transform 0.2s; }
        .submit-btn:hover { background-color: #0056b3; transform: translateY(-2px); }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Quill Editor specific height styling */
        #editor {
            height: 250px;
            background-color: #fff;
            border-radius: 6px;
        }
    </style>
</head>
<body onload="toggleInterval()">
    <div class="form-container">
        <h2>Schedule Event Entry</h2>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- We add an onsubmit listener to the form -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return handleSubmit()">
            <div class="form-group">
                <label for="next_schedule_date">Next Schedule Date:</label>
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
                <label>Notes (Use the editor below):</label>
                <!-- This div is where Quill renders the editor -->
                <div id="editor"></div>
            </div>
            
            <!-- Hidden input to store the Base64 version of the notes for submission -->
            <input type="hidden" id="notes_base64" name="notes_base64">

            <div class="form-group">
                <input type="submit" value="Save Schedule" class="submit-btn">
            </div>
        </form>
    </div>

    <!-- Include Quill JS from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        // Initialize Quill editor
        const quill = new Quill('#editor', {
            theme: 'snow', // 'snow' is a clean, email-like theme with a toolbar
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['link', 'image'], // Includes image button for uploads/pasting
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['clean'] // remove formatting button
                ]
            },
            placeholder: 'Enter detailed notes or paste images here...'
        });

        // Function to handle form submission and encode notes to Base64
        function handleSubmit() {
            const base64Input = document.getElementById('notes_base64');
            
            // Get the HTML content from the Quill editor
            const editorContentHtml = quill.root.innerHTML;

            // Encode the HTML string to Base64
            // Quill handles pasted images by converting them to data:image/base64 URLs internally
            base64Input.value = btoa(unescape(encodeURIComponent(editorContentHtml)));
            
            // The form will now submit the hidden input with the Base64 data
            return true; 
        }

        // JavaScript to toggle visibility of the interval selection (same as before)
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
</body>
</html>
