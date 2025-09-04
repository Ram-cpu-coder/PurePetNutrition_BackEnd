<?php

require_once "../config/cors.php";
header('Content-Type: application/json');
require_once '../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ---------------- GET ----------------
    case 'GET':
        // filter by rating
        $rating = $_GET['rating'] ?? 'all';

        if ($rating === 'all') {
            $sql = "SELECT id, name, message, rating, approved, created_at, updated_at 
                    FROM testimonials ORDER BY id DESC";
            $result = $conn->query($sql);
        } else {
            $stmt = $conn->prepare("SELECT id, name, message, rating, approved, created_at, updated_at 
                                    FROM testimonials WHERE rating = ? ORDER BY id DESC");
            $stmt->bind_param("i", $rating);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    // ---------------- POST ----------------
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'], $data['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: name, message']);
            exit;
        }

        $name    = $data['name'];
        $message = $data['message'];
        $rating  = isset($data['rating']) ? (int)$data['rating'] : null;
        $approved = isset($data['approved']) ? (int)$data['approved'] : 0;

        $stmt = $conn->prepare(
            "INSERT INTO testimonials (name, message, rating, approved) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssii", $name, $message, $rating, $approved);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Testimonial created', 'id' => $stmt->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    // ---------------- PUT ----------------
    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = $query['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Testimonial ID is required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        // --- Toggle-only update ---
        if (isset($data['approved']) && count($data) === 1) {
            $approved = (int)$data['approved'];
            $stmt = $conn->prepare("UPDATE testimonials SET approved=? WHERE id=?");
            $stmt->bind_param("ii", $approved, $id);
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Visibility updated']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }
            break;
        }

        // --- Full update ---
        if (!isset($data['name'], $data['message'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields: name, message']);
            exit;
        }

        $name    = $data['name'];
        $message = $data['message'];
        $rating  = isset($data['rating']) ? (int)$data['rating'] : null;
        $approved = isset($data['approved']) ? (int)$data['approved'] : 0;

        $stmt = $conn->prepare(
            "UPDATE testimonials SET name=?, message=?, rating=?, approved=? WHERE id=?"
        );
        $stmt->bind_param("ssiii", $name, $message, $rating, $approved, $id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Testimonial updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    // ---------------- DELETE ----------------
    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = $query['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Testimonial ID is required']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Testimonial deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}