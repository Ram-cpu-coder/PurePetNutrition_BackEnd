<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Detect HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ---------------- GET ----------------
    case 'GET':
        $category = $_GET['category'] ?? 'all';

        if ($category === 'all') {
            $sql = "SELECT id, name, category, description, image, price, stock_quantity, sku, created_at, updated_at 
                    FROM products ORDER BY id DESC";
            $result = $conn->query($sql);
        } else {
            $stmt = $conn->prepare("SELECT id, name, category, description, image, price, stock_quantity, sku, created_at, updated_at 
                                    FROM products WHERE category = ? ORDER BY id DESC");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    // ---------------- POST ----------------
  case 'POST':
    // read the payload
    $data = json_decode(file_get_contents("php://input"), true);

    // Validation of the required fields
    if (
        !isset($data['name']) ||
        !isset($data['category']) ||
        !isset($data['description']) ||
        !isset($data['image']) ||
        !isset($data['price'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // Assigned to variables 
    $name           = $data['name'];
    $category       = $data['category'];
    $description    = $data['description'];
    $image          = $data['image'];
    $price          = $data['price'];
    $stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
    $sku            = isset($data['sku']) ? $data['sku'] : null;

    
    $stmt = $conn->prepare(
        "INSERT INTO products (name, category, description, image, price, stock_quantity, sku) 
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssdis",
        $name,
        $category,
        $description,
        $image,
        $price,
        $stock_quantity,
        $sku
    );

    if ($stmt->execute()) {
        echo json_encode([
            'message' => 'Product created successfully',
            'id'      => $stmt->insert_id
        ]);
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
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, description=?, image=?, price=?, stock_quantity=?, sku=? WHERE id=?");
        $stmt->bind_param(
            "ssssdssi",
            $data['name'],
            $data['category'],
            $data['description'],
            $data['image'],
            $data['price'],
            $data['stock_quantity'],
            $data['sku'],
            $id
        );

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Product updated']);
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
            echo json_encode(['error' => 'Product ID is required']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Product deleted']);
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