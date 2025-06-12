<?php
// DB connection params
$servername = "localhost";
$username = "root"; // Change as needed
$password = "saimak0710";     // Change as needed
$dbname = "college_event_management";

// Connect DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo "Database connection failed";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $date = trim($_POST['date'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if (!$title || !$date || !$description) {
    http_response_code(400);
    echo "All fields are required.";
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO events (title, date, description) VALUES (?, ?, ?)");
  if (!$stmt) {
    http_response_code(500);
    echo "Prepare statement failed";
    exit;
  }
  $stmt->bind_param("sss", $title, $date, $description);
  if (!$stmt->execute()) {
    http_response_code(500);
    echo "Failed to insert event";
    $stmt->close();
    $conn->close();
    exit;
  }
  $stmt->close();
  $conn->close();
  http_response_code(200);
  echo "Event created";
  exit;
}
http_response_code(405);
echo "Method not allowed";
?>
