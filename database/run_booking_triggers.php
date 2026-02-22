<?php
/**
 * Run Database Triggers: Auto update has_booking column
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ralira_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected to database: $database\n";

// Drop existing triggers if they exist
$triggers = [
    'update_has_booking_on_booking_insert',
    'update_has_booking_on_booking_update', 
    'update_has_booking_on_booking_delete'
];

foreach ($triggers as $trigger) {
    $drop_sql = "DROP TRIGGER IF EXISTS $trigger";
    $conn->query($drop_sql);
    echo "Dropped existing trigger: $trigger\n";
}

// Create trigger for booking insert
$insert_trigger_sql = "
CREATE TRIGGER update_has_booking_on_booking_insert
AFTER INSERT ON consultation_bookings
FOR EACH ROW
BEGIN
    IF NEW.status_booking != 'canceled' THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END";

if ($conn->query($insert_trigger_sql)) {
    echo "Trigger 'update_has_booking_on_booking_insert' created successfully.\n";
} else {
    echo "Error creating insert trigger: " . $conn->error . "\n";
}

// Create trigger for booking update
$update_trigger_sql = "
CREATE TRIGGER update_has_booking_on_booking_update
AFTER UPDATE ON consultation_bookings
FOR EACH ROW
BEGIN
    IF NEW.status_booking = 'canceled' THEN
        IF NOT EXISTS (
            SELECT 1 FROM consultation_bookings 
            WHERE schedule_id = NEW.schedule_id 
            AND status_booking != 'canceled' 
            AND booking_id != NEW.booking_id
        ) THEN
            UPDATE psychologist_schedule_dates 
            SET has_booking = 0 
            WHERE schedule_date_id = NEW.schedule_id;
        END IF;
    ELSEIF NEW.status_booking != 'canceled' THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 1 
        WHERE schedule_date_id = NEW.schedule_id;
    END IF;
END";

if ($conn->query($update_trigger_sql)) {
    echo "Trigger 'update_has_booking_on_booking_update' created successfully.\n";
} else {
    echo "Error creating update trigger: " . $conn->error . "\n";
}

// Create trigger for booking delete
$delete_trigger_sql = "
CREATE TRIGGER update_has_booking_on_booking_delete
AFTER DELETE ON consultation_bookings
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM consultation_bookings 
        WHERE schedule_id = OLD.schedule_id 
        AND status_booking != 'canceled'
    ) THEN
        UPDATE psychologist_schedule_dates 
        SET has_booking = 0 
        WHERE schedule_date_id = OLD.schedule_id;
    END IF;
END";

if ($conn->query($delete_trigger_sql)) {
    echo "Trigger 'update_has_booking_on_booking_delete' created successfully.\n";
} else {
    echo "Error creating delete trigger: " . $conn->error . "\n";
}

// Show all triggers
echo "\nCurrent triggers:\n";
$result = $conn->query("SHOW TRIGGERS");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['Table'] === 'consultation_bookings') {
            echo "- Trigger: {$row['Trigger']}, Event: {$row['Event']}, Timing: {$row['Timing']}\n";
        }
    }
} else {
    echo "No triggers found.\n";
}

$conn->close();
echo "\nTriggers setup completed!\n";
?>
