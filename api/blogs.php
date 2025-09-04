<?php

require_once "../config/cors.php";
header('Content-Type: application/json');
require_once '../config/db.php';

// send JSON and exit
function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // filter by category
        $category = $_GET['category'] ?? 'all';
        if ($category === 'all') {
            $sql = "SELECT id, title, category, description FROM blogs ORDER BY id DESC";
            $result = $conn->query($sql);
        } else {
            $stmt = $conn->prepare("SELECT id, title, category, description FROM blogs WHERE category = ? ORDER BY id DESC");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        $blogs = $result->fetch_all(MYSQLI_ASSOC);
        respond($blogs);
        break;

   case 'POST':
    // Create new blog
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['title'], $input['category'], $input['description'], $input['image'])) {
        respond(['error' => 'Missing required fields'], 400);
    }
    $stmt = $conn->prepare("INSERT INTO blogs (title, category, description, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $input['title'], $input['category'], $input['description'], $input['image']);
    if ($stmt->execute()) {
        respond(['message' => 'Blog created', 'id' => $stmt->insert_id], 201);
    } else {
        respond(['error' => 'Failed to create blog'], 500);
    }
    break;

case 'PUT':
    // Update blog
    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'] ?? null;
    if (!$id) {
        respond(['error' => 'Missing blog ID'], 400);
    }
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['title'], $input['category'], $input['description'], $input['image'])) {
        respond(['error' => 'Missing required fields'], 400);
    }
    $stmt = $conn->prepare("UPDATE blogs SET title=?, category=?, description=?, image=? WHERE id=?");
    $stmt->bind_param("ssssi", $input['title'], $input['category'], $input['description'], $input['image'], $id);
    if ($stmt->execute()) {
        respond(['message' => 'Blog updated']);
    } else {
        respond(['error' => 'Failed to update blog'], 500);
    }
    break;

    case 'DELETE':
        // Delete blog
        parse_str($_SERVER['QUERY_STRING'], $query);
        $id = $query['id'] ?? null;
        if (!$id) {
            respond(['error' => 'Missing blog ID'], 400);
        }
        $stmt = $conn->prepare("DELETE FROM blogs WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            respond(['message' => 'Blog deleted']);
        } else {
            respond(['error' => 'Failed to delete blog'], 500);
        }
        break;

    default:
        respond(['error' => 'Method not allowed'], 405);
}