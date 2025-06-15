<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/cors.php';

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/');
    $segments = explode('/', $path);
    
    // Find the category ID
    $id = null;
    $categoryIndex = array_search('category', $segments);
    if ($categoryIndex !== false && isset($segments[$categoryIndex + 1])) {
        $id = $segments[$categoryIndex + 1];
        if (!is_numeric($id)) {
            $id = null;
        }
    }

    switch ($requestMethod) {
        case 'GET':
            if ($id) {
                // Get single category
                $stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($category) {
                    echo json_encode($category);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Category not found']);
                }
            } else {
                // Get all category
                $stmt = $pdo->query("SELECT * FROM category ORDER BY name");
                $category = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($category);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }
            
            if (!isset($input['name']) || empty(trim($input['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name is required']);
                break;
            }

            $stmt = $pdo->prepare("INSERT INTO category (name, description) VALUES (:name, :description)");
            $stmt->execute([
                ':name' => trim($input['name']),
                ':description' => $input['description'] ?? null
            ]);

            $id = $pdo->lastInsertId();
            http_response_code(201);
            echo json_encode([
                'id' => $id,
                'message' => 'Category created successfully'
            ]);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Category ID required']);
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                break;
            }
            
            if (!isset($input['name']) || empty(trim($input['name']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Category name is required']);
                break;
            }

            $stmt = $pdo->prepare("UPDATE category SET name = :name, description = :description WHERE id = :id");
            $stmt->execute([
                ':name' => trim($input['name']),
                ':description' => $input['description'] ?? null,
                ':id' => $id
            ]);

            // Return updated category
            $stmt = $pdo->prepare("SELECT * FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $updatedCategory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($updatedCategory) {
                http_response_code(200);
                echo json_encode($updatedCategory);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Category ID required']);
                break;
            }

            // Check if category is being used by any asset
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM asset WHERE category_id = :id");
            $stmt->execute([':id' => $id]);
            $assetCount = $stmt->fetchColumn();

            if ($assetCount > 0) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Cannot delete category',
                    'message' => "Category is being used by $assetCount asset(s)"
                ]);
                break;
            }

            // Check if category exists
            $stmt = $pdo->prepare("SELECT id FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM category WHERE id = :id");
            $stmt->execute([':id' => $id]);

            http_response_code(200);
            echo json_encode(['message' => 'Category deleted successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}