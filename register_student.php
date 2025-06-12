<?php
$servername = "localhost";
$username = "root"; // Change as needed
$password = "saimak0710";     // Change as needed
$dbname = "college_event_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo "Database connection failed";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo "Method Not Allowed";
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$event_id = (int)($_POST['event_id'] ?? 0);

if (!$name || !$email || !$event_id) {
  http_response_code(400);
  echo "All fields are required.";
  exit;
}

// Check event exists
$stmt_event = $conn->prepare("SELECT id FROM events WHERE id=?");
$stmt_event->bind_param("i", $event_id);
$stmt_event->execute();
$stmt_event->store_result();
if ($stmt_event->num_rows === 0) {
  http_response_code(400);
  echo "Selected event does not exist.";
  $stmt_event->close();
  $conn->close();
  exit;
}
$stmt_event->close();

// Check if student exists
$stmt_student = $conn->prepare("SELECT id FROM students WHERE email=?");
$stmt_student->bind_param("s", $email);
$stmt_student->execute();
$stmt_student->store_result();

if ($stmt_student->num_rows === 0) {
  // Insert student
  $stmt_student->close();
  $stmt_insert = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
  $stmt_insert->bind_param("ss", $name, $email);
  if (!$stmt_insert->execute()) {
    http_response_code(500);
    echo "Failed to register student.";
    $stmt_insert->close();
    $conn->close();
    exit;
  }
  $student_id = $stmt_insert->insert_id;
  $stmt_insert->close();
} else {
  // Get student id
  $stmt_student->bind_result($student_id);
  $stmt_student->fetch();
  $stmt_student->close();

  // Update name in case changed
  $stmt_update = $conn->prepare("UPDATE students SET name=? WHERE id=?");
  $stmt_update->bind_param("si", $name, $student_id);
  $stmt_update->execute();
  $stmt_update->close();
}

// Check if already registered
$stmt_check = $conn->prepare("SELECT id FROM registrations WHERE student_id=? AND event_id=?");
$stmt_check->bind_param("ii", $student_id, $event_id);
$stmt_check->execute();
$stmt_check->store_result();
if($stmt_check->num_rows > 0){
  http_response_code(400);
  echo "You have already registered for this event.";
  $stmt_check->close();
  $conn->close();
  exit;
}
$stmt_check->close();

// Register student for event
$stmt_reg = $conn->prepare("INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
$stmt_reg->bind_param("ii", $student_id, $event_id);
if (!$stmt_reg->execute()) {
  http_response_code(500);
  echo "Failed to register for event.";
  $stmt_reg->close();
  $conn->close();
  exit;
}

$stmt_reg->close();
$conn->close();

// Mock Email confirmation: just return success message (can extend for real mail)
http_response_code(200);
echo "Registration successful! A confirmation email has been sent to $email (mocked).";
exit;
?>
