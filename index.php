<?php
// Set headers for CORS and content-type handling
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Allow preflight OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$host = "localhost";
$db_name = "e_commerce_evermos"; // Replace with your database name
$username = "root";   // Replace with your database username
$password = "";       // Replace with your database password

// Connect to MySQL database using MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

// Check for connection errors
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

function route($method, $path, $handler)
{
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    // Adjust this to remove the base directory (if needed)
    $requestUri = str_replace("/php_api_ecommerce", "", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    // Match the HTTP method and path
    if ($requestMethod === $method && preg_match($path, $requestUri, $matches)) {
        $handler($matches);
        exit();
    }
}

// Fetch all records
function getAll($conn)
{
    $sql = "SELECT id, name, price, image_url FROM products"; // Adjust table name
    $result = $conn->query($sql);
    $data = [];

    if (!empty($result->num_rows) > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(array("status" => "success", "message" => "Data retreived successfully.", "error" => false, "data" => $data));
    } else {
        echo json_encode(["status" => "error", "message" => "No records found."]);
    }
}



function checkOut($mysqli)
{
    $data = json_decode(file_get_contents('php://input'));

    try {
        // Start the transaction
        $mysqli->begin_transaction();

        // Prepare the insert statement for the customer
        $insertSqlCustomer = "INSERT INTO customers (email, name, post_code) 
                              VALUES (?, ?, ?)";
        $insertStmt = $mysqli->prepare($insertSqlCustomer);

        // Bind parameters and execute the insert
        $insertStmt->bind_param("sss", $data->email, $data->name, $data->post_code);
        $insertStmt->execute();

        // Check if the insert was successful
        if ($insertStmt->affected_rows === 0) {
            echo json_encode(array("status" => "error", "message" => "Insert operation failed. Please retry the transaction.", "error" => true));
            return;
        }

        // Get the last inserted customer ID
        $insertedCustomerId = $mysqli->insert_id;

        // Loop through cart items and process each one
        foreach ($data->cart_items as $key => $value) {
            $productId = $value->id;
            $purchasedQuantity = $value->quantity;

            // Select the current stock and version
            $sql = "SELECT quantity, version FROM stock WHERE product_id = ? FOR UPDATE";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $stock = $result->fetch_assoc();

            if (!$stock) {
                echo json_encode(array("status" => "error", "message" => "Product with ID $productId not found in stock.", "error" => true));
                return;
            }

            $currentQuantity = $stock['quantity'];
            $currentVersion = $stock['version'];

            // Check if enough stock is available
            if ($currentQuantity < $purchasedQuantity) {
                echo json_encode(array("status" => "error", "message" => "Insufficient stock for product $productId.", "error" => true));
                return;
            }

            // Update the stock quantity and version
            $newQuantity = $currentQuantity - $purchasedQuantity;
            $newVersion = $currentVersion + 1;

            $updateSql = "UPDATE stock 
                          SET quantity = ?, version = ?, last_updated = CURRENT_TIMESTAMP 
                          WHERE product_id = ? AND version = ?";
            $updateStmt = $mysqli->prepare($updateSql);
            $updateStmt->bind_param("iiii", $newQuantity, $newVersion, $productId, $currentVersion);
            $updateStmt->execute();

            // Check if the update was successful
            if ($updateStmt->affected_rows === 0) {
                echo json_encode(array("status" => "error", "message" => "Optimistic locking failed. Please retry the transaction.", "error" => true));
                return;
            }

            // Insert the transaction record
            $insertSqlTransaction = "INSERT INTO transactions (product_id, quantity, customer_id) 
                                     VALUES (?, ?, ?)";
            $insertStmt = $mysqli->prepare($insertSqlTransaction);
            $insertStmt->bind_param("iii", $productId, $purchasedQuantity, $insertedCustomerId);
            $insertStmt->execute();

            // Check if the insert was successful
            if ($insertStmt->affected_rows === 0) {
                echo json_encode(array("status" => "error", "message" => "Insert operation failed. Please retry the transaction.", "error" => true));
                return;
            }
        }

        // Commit the transaction
        $mysqli->commit();
        echo json_encode(array("status" => "success", "message" => "Stock updated successfully!", "error" => false));
        return;
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $mysqli->rollback();
        echo json_encode(array("status" => "error", "message" => "Failed to update stock: " . $e->getMessage(), "error" => false));
        return;
    }
}



function getCart($conn)
{
    $inputData = file_get_contents('php://input');

    $data = json_decode($inputData, true);
    $productIds =   $data['productIds'];

    $sql = "SELECT id, name, price, image_url FROM products WHERE id IN ($productIds) ORDER BY id ";
    $result = $conn->query($sql);
    $data = [];

    if (!empty($result->num_rows) > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(array("status" => "success", "message" => "Data retreived successfully.", "error" => false, "data" => $data));
    } else {
        echo json_encode(["status" => "error", "message" => "No records found."]);
    }
}

// Define routes
route('GET', '~^/api/products$~', function () use ($conn) {
    getAll($conn);
});


route('POST', '~^/api/cart$~', function () use ($conn) {
    getCart($conn);
});

route('GET', '~^/api/products/(\d+)$~', function ($matches) use ($conn) {
    $id = intval($matches[1]);
    getOne($conn, $id);
});


// If no route matches, return 404
http_response_code(404);
echo json_encode(["message" => "Endpoint not found."]);

// Close the database connection
$conn->close();
