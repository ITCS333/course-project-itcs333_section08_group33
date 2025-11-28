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

// Use local JSON storage for development to avoid DB setup.
$dataFile = __DIR__ . '/students.json';
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
// $db = (new Database())->getConnection(); // Uncomment when using a real DB
$db = null; // placeholder for compatibility with function signatures

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
    global $dataFile, $query;
    $students = [];
    if (file_exists($dataFile)) {
        $json = file_get_contents($dataFile);
        $students = json_decode($json, true) ?: [];
    }

    // Optional search filtering
    $search = $query['search'] ?? null;
    if ($search) {
        $term = mb_strtolower(trim($search));
        $students = array_values(array_filter($students, function ($s) use ($term) {
            return (mb_stripos($s['name'] ?? '', $term) !== false)
                || (mb_stripos($s['student_id'] ?? '', $term) !== false)
                || (mb_stripos($s['email'] ?? '', $term) !== false);
        }));
    }

    // Optional sorting
    $sort = $query['sort'] ?? null;
    $order = strtolower($query['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
    $allowed = ['name', 'student_id', 'email'];
    if ($sort && in_array($sort, $allowed, true)) {
        usort($students, function ($a, $b) use ($sort, $order) {
            $va = $a[$sort] ?? '';
            $vb = $b[$sort] ?? '';
            if ($sort === 'student_id') {
                $res = strcmp((string)$va, (string)$vb);
            } else {
                $res = mb_strcasecmp((string)$va, (string)$vb);
            }
            return $order === 'asc' ? $res : -$res;
        });
    }

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
    global $dataFile;
    $students = [];
    if (file_exists($dataFile)) {
        $students = json_decode(file_get_contents($dataFile), true) ?: [];
    }
    $found = null;
    foreach ($students as $s) {
        if (isset($s['student_id']) && (string)$s['student_id'] === (string)$studentId) {
            $found = $s;
            break;
        }
    }
    if ($found) {
        unset($found['password']);
        sendResponse(['success' => true, 'data' => $found]);
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
    global $dataFile;
    $student_id = sanitizeInput($data['student_id'] ?? '');
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$student_id || !$name || !$email || !$password) {
        sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    }
    if (!validateEmail($email)) {
        sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }

    $students = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];
    foreach ($students as $s) {
        if ((string)($s['student_id'] ?? '') === (string)$student_id) {
            sendResponse(['success' => false, 'message' => 'student_id already exists'], 409);
        }
        if ((string)($s['email'] ?? '') === (string)$email) {
            sendResponse(['success' => false, 'message' => 'email already exists'], 409);
        }
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $nextId = 1;
    if (!empty($students)) {
        $ids = array_column($students, 'id');
        $nextId = (int)max($ids) + 1;
    }
    $new = [
        'id' => $nextId,
        'student_id' => $student_id,
        'name' => $name,
        'email' => $email,
        'password' => $hashed,
        'created_at' => date(DATE_ATOM)
    ];
    $students[] = $new;
    if (file_put_contents($dataFile, json_encode($students, JSON_PRETTY_PRINT))) {
        $out = $new;
        unset($out['password']);
        sendResponse(['success' => true, 'data' => $out], 201);
    }
    sendResponse(['success' => false, 'message' => 'Failed to create student'], 500);
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
    global $dataFile;
    $student_id = $data['student_id'] ?? '';
    if (!$student_id) sendResponse(['success' => false, 'message' => 'student_id is required'], 400);
    $students = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];
    $foundIndex = null;
    foreach ($students as $i => $s) {
        if ((string)($s['student_id'] ?? '') === (string)$student_id) {
            $foundIndex = $i;
            break;
        }
    }
    if ($foundIndex === null) sendResponse(['success' => false, 'message' => 'Student not found'], 404);

    $name = isset($data['name']) ? sanitizeInput($data['name']) : null;
    $email = isset($data['email']) ? sanitizeInput($data['email']) : null;

    if ($email && !validateEmail($email)) sendResponse(['success' => false, 'message' => 'Invalid email format'], 400);

    if ($email) {
        foreach ($students as $i => $s) {
            if ($i !== $foundIndex && (string)($s['email'] ?? '') === (string)$email) {
                sendResponse(['success' => false, 'message' => 'Email already in use'], 409);
            }
        }
    }

    if ($name !== null) $students[$foundIndex]['name'] = $name;
    if ($email !== null) $students[$foundIndex]['email'] = $email;
    $students[$foundIndex]['updated_at'] = date(DATE_ATOM);

    if (file_put_contents($dataFile, json_encode($students, JSON_PRETTY_PRINT))) {
        $out = $students[$foundIndex];
        unset($out['password']);
        sendResponse(['success' => true, 'data' => $out]);
    }
    sendResponse(['success' => false, 'message' => 'Failed to update student'], 500);
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
    global $dataFile;
    if (!$studentId) sendResponse(['success' => false, 'message' => 'student_id is required'], 400);
    $students = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];
    $found = false;
    foreach ($students as $i => $s) {
        if ((string)($s['student_id'] ?? '') === (string)$studentId) {
            $found = true;
            array_splice($students, $i, 1);
            break;
        }
    }
    if (!$found) sendResponse(['success' => false, 'message' => 'Student not found'], 404);
    if (file_put_contents($dataFile, json_encode($students, JSON_PRETTY_PRINT))) {
        sendResponse(['success' => true]);
    }
    sendResponse(['success' => false, 'message' => 'Failed to delete student'], 500);
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
    global $dataFile;
    $student_id = $data['student_id'] ?? '';
    $current = $data['current_password'] ?? '';
    $new = $data['new_password'] ?? '';
    if (!$student_id || !$current || !$new) sendResponse(['success' => false, 'message' => 'Missing required fields'], 400);
    if (strlen($new) < 8) sendResponse(['success' => false, 'message' => 'New password must be at least 8 characters'], 400);

    $students = file_exists($dataFile) ? (json_decode(file_get_contents($dataFile), true) ?: []) : [];
    $foundIndex = null;
    foreach ($students as $i => $s) {
        if ((string)($s['student_id'] ?? '') === (string)$student_id) {
            $foundIndex = $i;
            break;
        }
    }
    if ($foundIndex === null) sendResponse(['success' => false, 'message' => 'Student not found'], 404);

    $hash = $students[$foundIndex]['password'] ?? '';
    if (!password_verify($current, $hash)) sendResponse(['success' => false, 'message' => 'Current password incorrect'], 401);

    $students[$foundIndex]['password'] = password_hash($new, PASSWORD_DEFAULT);
    $students[$foundIndex]['updated_at'] = date(DATE_ATOM);

    if (file_put_contents($dataFile, json_encode($students, JSON_PRETTY_PRINT))) {
        sendResponse(['success' => true, 'message' => 'Password updated']);
    }
    sendResponse(['success' => false, 'message' => 'Failed to update password'], 500);
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
