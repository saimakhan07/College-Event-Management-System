<?php
$servername = "localhost";
$username = "root"; // Change if needed
$password = "saimak0710";     // Change if needed
$dbname = "college_event_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([]);
  exit;
}

header('Content-Type: application/json');

$result = $conn->query("SELECT id, title, date FROM events ORDER BY date ASC");
$events = [];

while ($row = $result->fetch_assoc()) {
  $events[] = [
    'id' => (int)$row['id'],
    'title' => $row['title'],
    'date' => $row['date'],
  ];
}

echo json_encode($events);

$conn->close();
?>
