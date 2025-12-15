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
    $allowedSort = ['name', 'student_id', 'email', 'created_at'];
    $sort = isset($query['sort']) && in_array($query['sort'], $allowedSort, true) ? $query['sort'] : 'created_at';
    $order = strtolower($query['order'] ?? 'asc');
    $order = ($order === 'desc') ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $sort $order";


    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->execute();
    $students = $stmt->fetchAll();

    sendResponse(['success' => true, 'data' => $students]);
}


/**
 * Function: Get a single student by student_id
 * Method: GET
 * 
 * Query Parameters:
 *   - student_id: The student's university ID
 */
function getStudentById($db, $studentId) {
    // TODO: Prepare SQL query to select student by student_id
    
    // TODO: Bind the student_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch the result
    
    // TODO: Check if student exists
    // If yes, return success response with student data
    // If no, return error response with 404 status
    // Implementation inserted below the TODO comments.
    $stmt = $db->prepare('SELECT id, student_id, name, email, created_at FROM students WHERE student_id = :sid LIMIT 1');
    $stmt->bindValue(':sid', (string)$studentId, PDO::PARAM_STR);
    $stmt->execute();
    $student = $stmt->fetch();

    if ($student) {
        sendResponse(['success' => true, 'data' => $student]);
    }
    sendResponse(['success' => false, 'message' => 'Student not found'], 404);
}


/**
 * Function: Create a new student
 * Method: POST
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (must be unique)
 *   - name: Student's full name
 *   - email: Student's email (must be unique)
 *   - password: Default password (will be hashed)
 */
function createStudent($db, $data) {
    // TODO: Validate required fields
    // Check if student_id, name, email, and password are provided
    // If any field is missing, return error response with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // Validate email format using filter_var()
    
    // TODO: Check if student_id or email already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    
    // TODO: Hash the password
    // Use password_hash() with PASSWORD_DEFAULT
    
    // TODO: Prepare INSERT query
    
    // TODO: Bind parameters
    // Bind student_id, name, email, and hashed password
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // If no, return error response with 500 status
    // Implementation inserted below the TODO comments.
    $student_id = sanitizeInput($data['student_id'] ?? '');
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = (string)($data['password'] ?? '');

    if ($student_id === '' || $name === '' || $email === '' || $password === '') {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }
    if (!validateEmail($email)) {
        sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }

    // Check duplicates (student_id or email)
    $dupStmt = $db->prepare('SELECT student_id, email FROM students WHERE student_id = :sid OR email = :email LIMIT 1');
    $dupStmt->execute([':sid' => $student_id, ':email' => $email]);
    $dup = $dupStmt->fetch();
    if ($dup) {
        if ((string)$dup['student_id'] === $student_id) {
            sendResponse(['success' => false, 'message' => 'student_id already exists'], 409);
        }
        if ((string)$dup['email'] === $email) {
            sendResponse(['success' => false, 'message' => 'email already exists'], 409);
        }
        sendResponse(['success' => false, 'message' => 'Duplicate record'], 409);
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $ins = $db->prepare('INSERT INTO students (student_id, name, email, password) VALUES (:sid, :name, :email, :pass)');
    $ok = $ins->execute([
        ':sid' => $student_id,
        ':name' => $name,
        ':email' => $email,
        ':pass' => $hashed,
    ]);

    if (!$ok) {
        sendResponse(['success' => false, 'message' => 'Failed to create student'], 500);
    }

    $id = (int)$db->lastInsertId();
    $outStmt = $db->prepare('SELECT id, student_id, name, email, created_at FROM students WHERE id = :id');
    $outStmt->execute([':id' => $id]);
    $created = $outStmt->fetch();
    sendResponse(['success' => true, 'data' => $created], 201);
}


/**
 * Function: Update an existing student
 * Method: PUT
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (to identify which student to update)
 *   - name: Updated student name (optional)
 *   - email: Updated student email (optional)
 */
function updateStudent($db, $data) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    
    // TODO: Check if student exists
    // Prepare and execute a SELECT query to find the student
    // If not found, return error response with 404 status
    
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
    
    // TODO: If email is being updated, check if new email already exists
    // Prepare and execute a SELECT query
    // Exclude the current student from the check
    // If duplicate found, return error response with 409 status
    
    // TODO: Bind parameters dynamically
    // Bind only the parameters that are being updated
    
    // TODO: Execute the query
    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
    // Implementation inserted below the TODO comments.
    $student_id = (string)($data['student_id'] ?? '');
    if ($student_id === '') {
        sendResponse(['success' => false, 'message' => 'student_id is required'], 400);
    }

    // Check if student exists
    $existsStmt = $db->prepare('SELECT id FROM students WHERE student_id = :sid LIMIT 1');
    $existsStmt->execute([':sid' => $student_id]);
    $existing = $existsStmt->fetch();
    if (!$existing) {
        sendResponse(['success' => false, 'message' => 'Student not found'], 404);
    }

    $fields = [];
    $params = [':sid' => $student_id];

    if (array_key_exists('name', $data) && is_string($data['name']) && trim($data['name']) !== '') {
    $name = sanitizeInput($data['name']);
    $fields[] = 'name = :name';
    $params[':name'] = $name;
}


    if (array_key_exists('email', $data)) {
        $email = sanitizeInput($data['email']);
        if (!validateEmail($email)) {
            sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);
        }
        // Check duplicate email excluding current student
        $dupEmail = $db->prepare('SELECT 1 FROM students WHERE email = :email AND student_id <> :sid LIMIT 1');
        $dupEmail->execute([':email' => $email, ':sid' => $student_id]);
        if ($dupEmail->fetch()) {
            sendResponse(['success' => false, 'message' => 'Email already in use'], 409);
        }
        $fields[] = 'email = :email';
        $params[':email'] = $email;
    }

    if (empty($fields)) {
        sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }

    $sql = 'UPDATE students SET ' . implode(', ', $fields) . ' WHERE student_id = :sid';
    $upd = $db->prepare($sql);
    $ok = $upd->execute($params);
    if (!$ok) {
        sendResponse(['success' => false, 'message' => 'Failed to update student'], 500);
    }

    $outStmt = $db->prepare('SELECT id, student_id, name, email, created_at FROM students WHERE student_id = :sid');
    $outStmt->execute([':sid' => $student_id]);
    sendResponse(['success' => true, 'data' => $outStmt->fetch()]);
}


/**
 * Function: Delete a student
 * Method: DELETE
 * 
 * Query Parameters or JSON Body:
 *   - student_id: The student's university ID
 */
function deleteStudent($db, $studentId) {
    // TODO: Validate that student_id is provided
    // If not, return error response with 400 status
    
    // TODO: Check if student exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    
    // TODO: Prepare DELETE query
    
    // TODO: Bind the student_id parameter
    
    // TODO: Execute the query
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    // Implementation inserted below the TODO comments.
    if (!$studentId) {
        sendResponse(['success' => false, 'message' => 'student_id is required'], 400);
    }

    $del = $db->prepare('DELETE FROM students WHERE student_id = :sid');
    $del->execute([':sid' => (string)$studentId]);
    if ($del->rowCount() === 0) {
        sendResponse(['success' => false, 'message' => 'Student not found'], 404);
    }
    sendResponse(['success' => true]);
}


/**
 * Function: Change password
 * Method: POST with action=change_password
 * 
 * Required JSON Body:
 *   - student_id: The student's university ID (identifies whose password to change)
 *   - current_password: The student's current password
 *   - new_password: The new password to set
 */
function changePassword($db, $data) {
    // TODO: Validate required fields
    // Check if student_id, current_password, and new_password are provided
    // If any field is missing, return error response with 400 status
    
    // TODO: Validate new password strength
    // Check minimum length (at least 8 characters)
    // If validation fails, return error response with 400 status
    
    // TODO: Retrieve current password hash from database
    // Prepare and execute SELECT query to get password
    
    // TODO: Verify current password
    // Use password_verify() to check if current_password matches the hash
    // If verification fails, return error response with 401 status (Unauthorized)
    
    // TODO: Hash the new password
    // Use password_hash() with PASSWORD_DEFAULT
    
    // TODO: Update password in database
    // Prepare UPDATE query
    
    // TODO: Bind parameters and execute
    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no, return error response with 500 status
    // Implementation inserted below the TODO comments.
    $student_id = (string)($data['student_id'] ?? '');
    $current = (string)($data['current_password'] ?? '');
    $new = (string)($data['new_password'] ?? '');

    if ($student_id === '' || $current === '' || $new === '') {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }
    if (strlen($new) < 8) {
        sendResponse(['success' => false, 'message' => 'New password must be at least 8 characters'], 400);
    }

    $stmt = $db->prepare('SELECT password FROM students WHERE student_id = :sid LIMIT 1');
    $stmt->execute([':sid' => $student_id]);
    $row = $stmt->fetch();
    if (!$row) {
        sendResponse(['success' => false, 'message' => 'Student not found'], 404);
    }
    $hash = (string)($row['password'] ?? '');
    if (!password_verify($current, $hash)) {
        sendResponse(['success' => false, 'message' => 'Current password incorrect'], 401);
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $upd = $db->prepare('UPDATE students SET password = :pass WHERE student_id = :sid');
    $ok = $upd->execute([':pass' => $newHash, ':sid' => $student_id]);
    if (!$ok) {
        sendResponse(['success' => false, 'message' => 'Failed to update password'], 500);
    }
    sendResponse(['success' => true, 'message' => 'Password updated']);
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on HTTP method
    if ($method === 'GET') {
        // TODO: Check if student_id is provided in query parameters
        // If yes, call getStudentById()
        // If no, call getStudents() to get all students (with optional search/sort)
        if (!empty($_GET['student_id'])) {
            getStudentById($db, $_GET['student_id']);
        }
        getStudents($db);
    } elseif ($method === 'POST') {
        // TODO: Check if this is a change password request
        // Look for action=change_password in query parameters
        // If yes, call changePassword()
        // If no, call createStudent()
        
        $action = $_GET['action'] ?? null;
        if ($action === 'change_password') {
            changePassword($db, $requestBody);
        }
        createStudent($db, $requestBody);
    } elseif ($method === 'PUT') {
        // TODO: Call updateStudent()
        updateStudent($db, $requestBody);
    } elseif ($method === 'DELETE') {
        // TODO: Get student_id from query parameter or request body
        // Call deleteStudent()
        $sid = $_GET['student_id'] ?? ($requestBody['student_id'] ?? null);
        deleteStudent($db, $sid);
    } else {
        // TODO: Return error for unsupported methods
        // Set HTTP status to 405 (Method Not Allowed)
        // Return JSON error message
        sendResponse(['success' => false, 'message' => 'Method Not Allowed'], 405);
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional)
    // Return generic error response with 500 status
    error_log('DB Error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Database error'], 500);
    
} catch (Exception $e) {
    // TODO: Handle general errors
    // Return error response with 500 status
    error_log('Error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}


// ============================================================================
// HELPER FUNCTIONS (Optional but Recommended)
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send
 * @param int $statusCode - HTTP status code
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    
    // TODO: Echo JSON encoded data
    
    // TODO: Exit to prevent further execution
    // Implementation (preserve TODO comments above):
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}


/**
 * Helper function to validate email format
 * 
 * @param string $email - Email address to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email) {
    // TODO: Use filter_var with FILTER_VALIDATE_EMAIL
    // Return true if valid, false otherwise
    // Implementation (preserve TODO comments above):
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    // TODO: Strip HTML tags using strip_tags()
    // TODO: Convert special characters using htmlspecialchars()
    // Return sanitized data
    // Implementation (preserve TODO comments above):
    $s = is_string($data) ? $data : '';
    $s = trim($s);
    $s = strip_tags($s);
    $s = htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return $s;
}

?>
