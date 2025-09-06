<?php
header("Content-Type: application/json");

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Only POST method allowed"]);
    exit;
}

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$message = trim($data["message"] ?? "");

// Basic validation
if (!$name || !$email || !$message) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address."]);
    exit;
}

// Email settings
$to = "12200082@students.koi.edu.au"; 
$subject = "New Contact Form Submission";
$body = "You have a new message from the contact form:\n\n"
      . "Name: $name\n"
      . "Email: $email\n\n"
      . "Message:\n$message\n";
$headers = "From: 12200082@students.koi.edu.au\r\nReply-To: $email";

// Send email
if (mail($to, $subject, $body, $headers)) {
    echo json_encode(["success" => true, "message" => "Thanks! We received your message."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send email."]);
}
?>
