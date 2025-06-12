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

// Fetch all events
$events_result = $conn->query("SELECT id, title, date, description FROM events ORDER BY date DESC");
$events = [];

while ($row = $events_result->fetch_assoc()) {
  $event_id = $row['id'];
  
  // Fetch participants for each event
  $participants_result = $conn->prepare("SELECT s.name, s.email, r.registration_date 
    FROM registrations r 
    JOIN students s ON r.student_id = s.id 
    WHERE r.event_id = ? ORDER BY r.registration_date DESC");
  $participants_result->bind_param("i", $event_id);
  $participants_result->execute();
  $result = $participants_result->get_result();
  $participants = [];
  while ($p = $result->fetch_assoc()) {
    $participants[] = $p;
  }
  $participants_result->close();

  $events[] = [
    'id' => (int)$row['id'],
    'title' => $row['title'],
    'date' => $row['date'],
    'description' => $row['description'],
    'participants' => $participants
  ];
}

echo json_encode($events);
$conn->close();
?>
