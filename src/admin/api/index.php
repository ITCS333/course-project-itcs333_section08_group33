<?php
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structure (for reference):
 * Table: students
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - student_id (VARCHAR(50), UNIQUE) - The student's university ID
 *   - name (VARCHAR(100))
 *   - email (VARCHAR(100), UNIQUE)
 *   - password (VARCHAR(255)) - Hashed password
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve student(s)
 *   - POST: Create a new student OR change password
 *   - PUT: Update an existing student
 *   - DELETE: Delete a student
 * 
 * Response Format: JSON
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['last_api_hit'] = time();

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)


// Implementation (preserve TODO comments above):
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Database connection settings (must match your MariaDB setup)
// DB: course | User: admin | Pass: password123 | Host: 127.0.0.1
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'course';
$DB_USER = getenv('DB_USER') ?: 'admin';
$DB_PASS = getenv('DB_PASS') ?: 'password123';

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit


// Implementation (preserve TODO comment above):
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance


// Implementation (preserve TODO comment above):
// If you have a Database class, include it here. For now we use file-backed storage.
// require_once __DIR__ . '/../../lib/Database.php';

// TODO: Get the PDO database connection


// Implementation (preserve TODO comment above):
// $db = (new Database())->getConnection(); // If you have a Database class, you can use it.
// Otherwise we create a PDO connection directly.
try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $db = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Database connection failed'], 500);
}

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']


// Implementation (preserve TODO comment above):
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()


// Implementation (preserve TODO comment above):
$rawInput = file_get_contents('php://input');
$requestBody = json_decode($rawInput, true) ?: [];

// TODO: Parse query parameters for filtering and searching


// Implementation (preserve TODO comment above):
$query = $_GET ?? [];

/**
 * Function: Get all students or search for specific students
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by name, student_id, or email
 *   - sort: Optional field to sort by (name, student_id, email)
 *   - order: Optional sort order (asc or desc)
 */
function getStudents($db) {
    // TODO: Check if search parameter exists
    // If yes, prepare SQL query with WHERE clause using LIKE
    // Search should work on name, student_id, and email fields
    
    // TODO: Check if sort and order parameters exist
    // If yes, add ORDER BY clause to the query
    // Validate sort field to prevent SQL injection (only allow: name, student_id, email)
    // Validate order to prevent SQL injection (only allow: asc, desc)
    
    // TODO: Prepare the SQL query using PDO
    // Note: Do NOT select the password field
    
    // TODO: Bind parameters if using search
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response with success status and data
    // Implementation inserted below the TODO comments.
    global $query;

    // Search & filter
    $search = isset($query['search']) ? trim((string)$query['search']) : '';

    // Sort validation to prevent SQL injection
    $allowedSort = ['name', 'student_id', 'email'];
    $sort = isset($query['sort']) && in_array($query['sort'], $allowedSort, true) ? $query['sort'] : 'created_at';
    $order = strtolower($query['order'] ?? 'asc');
    $order = ($order === 'desc') ? 'DESC' : 'ASC';

    $sql = "SELECT id, student_id, name, email, created_at FROM students";
    $params = [];
    if ($search !== '') {
        $sql .= " WHERE name LIKE :q OR student_id LIKE :q OR email LIKE :q";
        $params[':q'] = '%' . $search . '%';
    }

    // If user didn't provide a valid sort, default to created_at
    if (!in_array($sort, $allowedSort, true)) {
        $sort = 'created_at';
    }
    $sql .= " ORDER BY {$sort} {$order}";

    $stmt = $db->prepare($sql);
    foreach ($param
