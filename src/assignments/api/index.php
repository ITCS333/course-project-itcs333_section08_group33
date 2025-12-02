<?php
/**
 * Assignment Management API
 * 
 * This is a RESTful API that handles all CRUD operations for course assignments
 * and their associated discussion comments.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: assignments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - title (VARCHAR(200))
 *   - description (TEXT)
 *   - due_date (DATE)
 *   - files (TEXT)
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - assignment_id (VARCHAR(50), FOREIGN KEY)
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve assignment(s) or comment(s)
 *   - POST: Create a new assignment or comment
 *   - PUT: Update an existing assignment
 *   - DELETE: Delete an assignment or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// HEADERS AND CORS CONFIGURATION
// ============================================================================

// TODO: Set Content-Type header to application/json
header('Content-Type: application/json; charset=UTF-8');

// TODO: Set CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');

// TODO: Handle preflight OPTIONS request
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// TODO: Include the database connection class
class Database {
    public function getConnection() {
        // TODO: Replace with your real XAMPP credentials if different
        $dsn  = 'mysql:host=$host;dbname=$db;'; // may need update
        $user = 'admin'; // may need update
        $pass = 'password123'; // may need update
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
        ];
        return new PDO($dsn, $user, $pass, $options);
    }
}

// TODO: Create database connection
try {
    $db = (new Database())->getConnection();
} 
// TODO: Set PDO to throw exceptions on errors
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}



// ============================================================================
// REQUEST PARSING
// ============================================================================

// TODO: Get the HTTP request method
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// TODO: Get the request body for POST and PUT requests
$rawBody = file_get_contents('php://input');

// TODO: Parse query parameters
$data    = json_decode($rawBody); // object style, consistent with samples
$query   = $_GET ?? [];


// ============================================================================
// ASSIGNMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all assignments
 * Method: GET
 * Endpoint: ?resource=assignments
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, due_date, created_at)
 *   - order: Optional sort order (asc or desc, default: asc)
 * 
 * Response: JSON array of assignment objects
 */
function getAllAssignments($db) {
    // TODO: Start building the SQL query
    $sql = "SELECT id, title, description, due_date, files, created_at, updated_at FROM assignments";
    $where  = [];
    $params = [];
    
    // TODO: Check if 'search' query parameter exists in $_GET
    if (!empty($_GET['search'])) {
        $where[]  = "(title LIKE ? OR description LIKE ?)";
        $term     = "%" . $_GET['search'] . "%";
        $params[] = $term;
        $params[] = $term;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    
    // TODO: Check if 'sort' and 'order' query parameters exist
    $allowedSort = ['title','due_date','created_at'];
    $sort  = (isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort, true)) ? $_GET['sort'] : 'created_at';
    $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'DESC' : 'ASC';
    $sql  .= " ORDER BY $sort $order";
    
    // TODO: Prepare the SQL statement using $db->prepare()
    $stmt = $db->prepare($sql);
    
    // TODO: Bind parameters if search is used
    foreach ($params as $i => $val) {
        $stmt->bindValue($i+1, $val, PDO::PARAM_STR);
    }
    
    // TODO: Execute the prepared statement
    $stmt->execute();
    
    // TODO: Fetch all results as associative array
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // TODO: For each assignment, decode the 'files' field from JSON to array
    foreach ($rows as &$row) {
        $files = $row['files'] ?? null;
        if (is_string($files) && $files !== '') {
            $decoded = json_decode($files, true);
            $row['files'] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } else {
            $row['files'] = [];
        }
    }
    unset($row);
    
    // TODO: Return JSON response
    sendResponse(['success' => true, 'data' => $rows]);
}


/**
 * Function: Get a single assignment by ID
 * Method: GET
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: The assignment ID (required)
 * 
 * Response: JSON object with assignment details
 */
function getAssignmentById($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if ($assignmentId === null || $assignmentId === '' || !ctype_digit((string)$assignmentId)) {
        sendResponse(['success'=>false,'message'=>'Invalid or missing id'],400);
    }
    $id = (int)$assignmentId;
    
    // TODO: Prepare SQL query to select assignment by id
    $sql = "SELECT id, title, description, due_date, files, created_at, updated_at FROM assignments WHERE id = :id LIMIT 1";

    
    // TODO: Bind the :id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Fetch the result as associative array
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    
    // TODO: Check if assignment was found
    if (!$row) {
        sendResponse(['success'=>false,'message'=>'Assignment not found'],404);
    }
    
    // TODO: Decode the 'files' field from JSON to array
    $files = $row['files'] ?? null;
    if (is_string($files) && $files !== '') {
        $decoded = json_decode($files, true);
        $row['files'] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
    } else {
        $row['files'] = [];
    }
    
    // TODO: Return success response with assignment data
    sendResponse(['success'=>true,'data'=>$row]);

}


/**
 * Function: Create a new assignment
 * Method: POST
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - title: Assignment title (required)
 *   - description: Assignment description (required)
 *   - due_date: Due date in YYYY-MM-DD format (required)
 *   - files: Array of file URLs/paths (optional)
 * 
 * Response: JSON object with created assignment data
 */
function createAssignment($db, $data) {
    // TODO: Validate required fields
    if (empty($data->title) || empty($data->description) || empty($data->due_date)) {
        sendResponse(['success'=>false,'message'=>'Missing required fields: title, description, due_date'],400);
    }
    
    // TODO: Sanitize input data
    $title       = sanitizeInput($data->title);
    $description = sanitizeInput($data->description);
    $due_date    = trim($data->due_date);
    
    // TODO: Validate due_date format
     if (!validateDate($due_date)) {
        sendResponse(['success'=>false,'message'=>'Invalid due_date format. Must be YYYY-MM-DD'],400);
    }
    
    // TODO: Generate a unique assignment ID
    // (We rely on AUTO_INCREMENT id; no separate assignment_id used here)

    
    // TODO: Handle the 'files' field
    $filesArray = $data->files ?? [];
    if (!is_array($filesArray)) {
        sendResponse(['success'=>false,'message'=>'files must be an array'],400);
    }
    $filesJson = json_encode($filesArray);
    
    // TODO: Prepare INSERT query
    $sql = "INSERT INTO assignments (title, description, due_date, files) VALUES (:title, :description, :due_date, :files)";

    
    // TODO: Bind all parameters
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':due_date', $due_date, PDO::PARAM_STR);
    $stmt->bindValue(':files', $filesJson, PDO::PARAM_STR);
    
    // TODO: Execute the statement
    if ($stmt->execute()) {
        $newId = (int)$db->lastInsertId();
    
    // TODO: Check if insert was successful
        $out = [
            'id'          => $newId,
            'title'       => $title,
            'description' => $description,
            'due_date'    => $due_date,
            'files'       => $filesArray,
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        sendResponse(['success'=>true,'data'=>$out],201);
    }
    
    // TODO: If insert failed, return 500 error
    sendResponse(['success'=>false,'message'=>'Failed to create assignment'],500);

}


/**
 * Function: Update an existing assignment
 * Method: PUT
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - id: Assignment ID (required, to identify which assignment to update)
 *   - title: Updated title (optional)
 *   - description: Updated description (optional)
 *   - due_date: Updated due date (optional)
 *   - files: Updated files array (optional)
 * 
 * Response: JSON object with success status
 */
function updateAssignment($db, $data) {
    // TODO: Validate that 'id' is provided in $data
    if (empty($data->id) || !ctype_digit((string)$data->id)) {
        sendResponse(['success'=>false,'message'=>'id is required'],400);
    }
    
    // TODO: Store assignment ID in variable
    $id = (int)$data->id;

    
    // TODO: Check if assignment exists
    $check = $db->prepare('SELECT id FROM assignments WHERE id = ? LIMIT 1');
    $check->bindValue(1, $id, PDO::PARAM_INT);
    $check->execute();
    if ($check->rowCount() === 0) {
        sendResponse(['success'=>false,'message'=>'Assignment not found'],404);
    }
    
    // TODO: Build UPDATE query dynamically based on provided fields
    $set  = [];
    $vals = [];
    
    // TODO: Check which fields are provided and add to SET clause
    if (isset($data->title)) {
        $set[]  = 'title = ?';
        $vals[] = sanitizeInput($data->title);
    }
    if (isset($data->description)) {
        $set[]  = 'description = ?';
        $vals[] = sanitizeInput($data->description);
    }
    if (isset($data->due_date)) {
        $date = trim($data->due_date);
        if (!validateDate($date)) {
            sendResponse(['success'=>false,'message'=>'Invalid due_date format. Must be YYYY-MM-DD'],400);
        }
        $set[]  = 'due_date = ?';
        $vals[] = $date;
    }
    if (isset($data->files)) {
        if (!is_array($data->files)) {
            sendResponse(['success'=>false,'message'=>'files must be an array'],400);
        }
        $set[]  = 'files = ?';
        $vals[] = json_encode($data->files);
    }
    
    // TODO: If no fields to update (besides updated_at), return 400 error
    if (empty($set)) {
        sendResponse(['success'=>false,'message'=>'No fields provided for update'],400);
    }

    
    // TODO: Complete the UPDATE query
    $set[] = 'updated_at = CURRENT_TIMESTAMP';
    $sql   = 'UPDATE assignments SET ' . implode(', ', $set) . ' WHERE id = ?';

    
    // TODO: Prepare the statement
    $stmt = $db->prepare($sql);

    
    // TODO: Bind all parameters dynamically
    foreach ($vals as $i => $val) {
        $stmt->bindValue($i+1, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(count($vals)+1, $id, PDO::PARAM_INT);
    
    // TODO: Execute the statement
    if ($stmt->execute()) {

    // TODO: Check if update was successful
    getAssignmentById($db, $id);
    }
    
    // TODO: If no rows affected, return appropriate message
    sendResponse(['success'=>false,'message'=>'Failed to update assignment'],500);

}


/**
 * Function: Delete an assignment
 * Method: DELETE
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: Assignment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if ($assignmentId === null || $assignmentId === '' || !ctype_digit((string)$assignmentId)) {
        sendResponse(['success'=>false,'message'=>'Invalid or missing id'],400);
    }
    $id = (int)$assignmentId;
    
    // TODO: Check if assignment exists
    $check = $db->prepare('SELECT id FROM assignments WHERE id = ? LIMIT 1');
    $check->bindValue(1, $id, PDO::PARAM_INT);
    $check->execute();
    if ($check->rowCount() === 0) {
        sendResponse(['success'=>false,'message'=>'Assignment not found'],404);
    }
    
    // TODO: Delete associated comments first (due to foreign key constraint)
    $delComments = $db->prepare('DELETE FROM comments WHERE assignment_id = ?');
    $delComments->bindValue(1, (string)$id, PDO::PARAM_STR);
    $delComments->execute();
    $commentsDeleted = $delComments->rowCount();
    
    // TODO: Prepare DELETE query for assignment
    $stmt = $db->prepare('DELETE FROM assignments WHERE id = ?');

    
    // TODO: Bind the :id parameter
    $stmt->bindValue(1, $id, PDO::PARAM_INT);

    
    // TODO: Execute the statement
    if ($stmt->execute() && $stmt->rowCount() > 0) {
    
    // TODO: Check if delete was successful
    sendResponse(['success'=>true,'message'=>"Assignment {$id} deleted, {$commentsDeleted} comments removed."]);
    }
    
    // TODO: If delete failed, return 500 error
    sendResponse(['success'=>false,'message'=>'Failed to delete assignment'],500);

}


// ============================================================================
// COMMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all comments for a specific assignment
 * Method: GET
 * Endpoint: ?resource=comments&assignment_id={assignment_id}
 * 
 * Query Parameters:
 *   - assignment_id: The assignment ID (required)
 * 
 * Response: JSON array of comment objects
 */
function getCommentsByAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (empty($assignmentId)) {
        sendResponse(['error' => 'assignment_id is required'], 400);
    }
    
    // TODO: Prepare SQL query to select all comments for the assignment
    $stmt = $db->prepare("SELECT * FROM comments WHERE assignment_id = :assignment_id");

    
    // TODO: Bind the :assignment_id parameter
    $stmt->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Fetch all results as associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    // TODO: Return success response with comments data
    sendResponse(['comments' => $comments]);

}


/**
 * Function: Create a new comment
 * Method: POST
 * Endpoint: ?resource=comments
 * 
 * Required JSON Body:
 *   - assignment_id: Assignment ID (required)
 *   - author: Comment author name (required)
 *   - text: Comment content (required)
 * 
 * Response: JSON object with created comment data
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    if (empty($data->assignment_id) || empty($data->author) || empty($data->text)) {
        sendResponse(['error' => 'assignment_id, author, and text are required'], 400);
    }
    
    // TODO: Sanitize input data
    $assignmentId = (int)$data->assignment_id;
    $author = sanitizeInput($data->author);
    $text = sanitizeInput($data->text);
    
    // TODO: Validate that text is not empty after trimming
    if (trim($text) === '') {
        sendResponse(['error' => 'Comment text cannot be empty'], 400);
    }
    
    // TODO: Verify that the assignment exists
    $checkStmt = $db->prepare("SELECT id FROM assignments WHERE id = :id");
    $checkStmt->bindParam(':id', $assignmentId, PDO::PARAM_INT);
    $checkStmt->execute();
    if ($checkStmt->rowCount() === 0) {
        sendResponse(['error' => 'Assignment does not exist'], 404);
    }
    
    // TODO: Prepare INSERT query for comment
    $stmt = $db->prepare("INSERT INTO comments (assignment_id, author, text) VALUES (:assignment_id, :author, :text)");

    
    // TODO: Bind all parameters
    $stmt->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':text', $text);
    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Get the ID of the inserted comment
    $commentId = $db->lastInsertId();

    
    // TODO: Return success response with created comment data
    sendResponse([
        'message' => 'Comment created successfully',
        'comment' => [
            'id' => $commentId,
            'assignment_id' => $assignmentId,
            'author' => $author,
            'text' => $text
        ]
    ], 201);
}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Endpoint: ?resource=comments&id={comment_id}
 * 
 * Query Parameters:
 *   - id: Comment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that $commentId is provided and not empty
    if (empty($commentId)) {
        sendResponse(['error' => 'Comment ID is required'], 400);
    }
    
    // TODO: Check if comment exists
    $checkStmt = $db->prepare("SELECT id FROM comments WHERE id = :id");
    $checkStmt->bindParam(':id', $commentId, PDO::PARAM_INT);
    $checkStmt->execute();
    if ($checkStmt->rowCount() === 0) {
        sendResponse(['error' => 'Comment not found'], 404);
    }
    
    // TODO: Prepare DELETE query
    $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");

    
    // TODO: Bind the :id parameter
    $stmt->bindParam(':id', $commentId, PDO::PARAM_INT);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Check if delete was successful
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'Failed to delete comment'], 500);
    }
    
    // TODO: If delete failed, return 500 error
    sendResponse(['message' => 'Comment deleted successfully']);

}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Get the 'resource' query parameter to determine which resource to access
    $resource = $_GET['resource'] ?? null;

    
    // TODO: Route based on HTTP method and resource type
    
    if ($method === 'GET') {
        // TODO: Handle GET requests
        
        if ($resource === 'assignments') {
            // TODO: Check if 'id' query parameter exists
            
        } elseif ($resource === 'comments') {
            // TODO: Check if 'assignment_id' query parameter exists
            $assignmentId = $_GET['assignment_id'] ?? null;
            getCommentsByAssignment($db, $assignmentId);
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid resource'], 400);
        }
        
    } elseif ($method === 'POST') {
        // TODO: Handle POST requests (create operations)
        
        if ($resource === 'assignments') {
            // TODO: Call createAssignment($db, $data)
            
        } elseif ($resource === 'comments') {
            // TODO: Call createComment($db, $data)
            createComment($db, $data);
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid resource'], 400);
        }
        
    } elseif ($method === 'PUT') {
        // TODO: Handle PUT requests (update operations)
        
        if ($resource === 'assignments') {
            // TODO: Call updateAssignment($db, $data)
            
        } else {
            // TODO: PUT not supported for other resources
            sendResponse(['error' => 'PUT not supported for this resource'], 405);

        }
        
    } elseif ($method === 'DELETE') {
        // TODO: Handle DELETE requests
        
        if ($resource === 'assignments') {
            // TODO: Get 'id' from query parameter or request body
            
        } elseif ($resource === 'comments') {
            // TODO: Get comment 'id' from query parameter
            $commentId = $_GET['id'] ?? null;
            deleteComment($db, $commentId);
        } else {
            // TODO: Invalid resource, return 400 error
            sendResponse(['error' => 'Invalid resource'], 400);

        }
        
    } else {
        // TODO: Method not supported
        sendResponse(['error' => 'Method not supported'], 405);

    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    sendResponse(['error' => 'Database error: ' . $e->getMessage()], 500);

} catch (Exception $e) {
    // TODO: Handle general errors
    sendResponse(['error' => 'Server error: ' . $e->getMessage()], 500);

}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param array $data - Data to send as JSON
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    http_response_code($statusCode);

    
    // TODO: Ensure data is an array
    if (!is_array($data)) {
        $data = ['data' => $data];
    }
    
    // TODO: Echo JSON encoded data
    header('Content-Type: application/json');
    echo json_encode($data);
    
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace from beginning and end
    $data = trim($data);

    
    // TODO: Remove HTML and PHP tags
    $data = strip_tags($data);

    
    // TODO: Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    
    // TODO: Return the sanitized data
    return $data;

}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat to validate
    $d = DateTime::createFromFormat('Y-m-d', $date);

    
    // TODO: Return true if valid, false otherwise
    return $d && $d->format('Y-m-d') === $date;

}


/**
 * Helper function to validate allowed values (for sort fields, order, etc.)
 * 
 * @param string $value - Value to validate
 * @param array $allowedValues - Array of allowed values
 * @return bool - True if valid, false otherwise
 */
function validateAllowedValue($value, $allowedValues) {
    // TODO: Check if $value exists in $allowedValues array
    $isValid = in_array($value, $allowedValues, true);

    // TODO: Return the result
    return $isValid;
    
}

?>
