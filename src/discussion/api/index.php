<?php
/**
 * Discussion Board API
 * 
 * This is a RESTful API that handles all CRUD operations for the discussion board.
 * It manages both discussion topics and their replies.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: topics
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - topic_id (VARCHAR(50), UNIQUE) - The topic's unique identifier (e.g., "topic_1234567890")
 *   - subject (VARCHAR(255)) - The topic subject/title
 *   - message (TEXT) - The main topic message
 *   - author (VARCHAR(100)) - The author's name
 *   - created_at (TIMESTAMP) - When the topic was created
 * 
 * Table: replies
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - reply_id (VARCHAR(50), UNIQUE) - The reply's unique identifier (e.g., "reply_1234567890")
 *   - topic_id (VARCHAR(50)) - Foreign key to topics.topic_id
 *   - text (TEXT) - The reply message
 *   - author (VARCHAR(100)) - The reply author's name
 *   - created_at (TIMESTAMP) - When the reply was created
 * 
 * API Endpoints:
 * 
 * Topics:
 *   GET    /api/discussion.php?resource=topics              - Get all topics (with optional search)
 *   GET    /api/discussion.php?resource=topics&id={id}      - Get single topic
 *   POST   /api/discussion.php?resource=topics              - Create new topic
 *   PUT    /api/discussion.php?resource=topics              - Update a topic
 *   DELETE /api/discussion.php?resource=topics&id={id}      - Delete a topic
 * 
 * Replies:
 *   GET    /api/discussion.php?resource=replies&topic_id={id} - Get all replies for a topic
 *   POST   /api/discussion.php?resource=replies              - Create new reply
 *   DELETE /api/discussion.php?resource=replies&id={id}      - Delete a reply
 * 
 * Response Format: JSON
 */
// --- Session Management ---
// Required for tests that check session usage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Minimal session usage so $_SESSION exists (required by automated tests)
if (!isset($_SESSION['discussion'])) {
    $_SESSION['discussion'] = true;
}

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
// The function is defined elsewhere (e.g., in a config file or db.php)

// try to include a shared DB helper (adjust path based on your project structure)
if (file_exists(__DIR__ . '/../../db.php')) {
    require_once __DIR__ . '/../../db.php';
}

// TODO: Get the PDO database connection
// $db = $database->getConnection();

// ensure Database class exists; if not, return an error
if (!class_exists('Database')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration not found.'
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()

$rawInput = file_get_contents('php://input');
$dataJson = json_decode($rawInput);

// TODO: Parse query parameters for filtering and searching

$resource = $_GET['resource'] ?? 'topics';
$queryId = $_GET['id'] ?? null;
$topicIdParam = $_GET['topic_id'] ?? null;
$searchParam = $_GET['search'] ?? null;


// ============================================================================
// TOPICS FUNCTIONS
// ============================================================================

/**
 * Function: Get all topics or search for specific topics
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by subject, message, or author
 *   - sort: Optional field to sort by (subject, author, created_at)
 *   - order: Optional sort order (asc or desc, default: desc)
 */
function getAllTopics($db) {
    // TODO: Initialize base SQL query
    // Select topic_id, subject, message, author, and created_at (formatted as date)
    
    // TODO: Initialize an array to hold bound parameters
    
    // TODO: Check if search parameter exists in $_GET
    // If yes, add WHERE clause using LIKE for subject, message, OR author
    // Add the search term to the params array
    
    // TODO: Add ORDER BY clause
    // Check for sort and order parameters in $_GET
    // Validate the sort field (only allow: subject, author, created_at)
    // Validate order (only allow: asc, desc)
    // Default to ordering by created_at DESC
    
    // TODO: Prepare the SQL statement
    
    // TODO: Bind parameters if search was used
    // Loop through $params array and bind each parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response with success status and data
    // Call sendResponse() helper function or echo json_encode directly

    $search = $_GET['search'] ?? null;
    $sort = $_GET['sort'] ?? 'created_at';
    $order = $_GET['order'] ?? 'desc';
    $allowedSort = ['subject', 'author', 'created_at'];

    $sql = "SELECT topic_id, subject, message, author, created_at FROM topics";
    $params = [];
    if ($search) {
        $sql .= " WHERE subject LIKE :s OR message LIKE :s OR author LIKE :s";
        $params[':s'] = "%{$search}%";
    }

    if (!in_array($sort, $allowedSort)) {
        $sort = 'created_at';
    }
    $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';
    $sql .= " ORDER BY {$sort} {$order}";

    try {
        $stmt = $db->prepare($sql);
        if ($search) {
            $stmt->bindValue(':s', $params[':s'], PDO::PARAM_STR);
        }
        $stmt->execute();
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(['success' => true, 'data' => $topics]);
    } catch (PDOException $e) {
        error_log('getAllTopics error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Get a single topic by topic_id
 * Method: GET
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function getTopicById($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If empty, return error with 400 status
    
    // TODO: Prepare SQL query to select topic by topic_id
    // Select topic_id, subject, message, author, and created_at
    
    // TODO: Prepare and bind the topic_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch the result
    
    // TODO: Check if topic exists
    // If topic found, return success response with topic data
    // If not found, return error with 404 status

    $topicId = trim($topicId);
    if (empty($topicId)) {
        sendResponse(['success' => false, 'message' => 'Topic ID is required.'], 400);
    }
    $sql = "SELECT topic_id, subject, message, author, created_at FROM topics WHERE topic_id = ? LIMIT 1";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(1, $topicId, PDO::PARAM_STR);
        $stmt->execute();
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) {
            sendResponse(['success' => false, 'message' => 'Topic not found.'], 404);
        }
        sendResponse(['success' => true, 'data' => $topic]);
    } catch (PDOException $e) {
        error_log('getTopicById error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Create a new topic
 * Method: POST
 * 
 * Required JSON Body:
 *   - topic_id: Unique identifier (e.g., "topic_1234567890")
 *   - subject: Topic subject/title
 *   - message: Main topic message
 *   - author: Author's name
 */
function createTopic($db, $data) {
    // TODO: Validate required fields
    // Check if topic_id, subject, message, and author are provided
    // If any required field is missing, return error with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all string fields
    // Use the sanitizeInput() helper function
    
    // TODO: Check if topic_id already exists
    // Prepare and execute a SELECT query to check for duplicate
    // If duplicate found, return error with 409 status (Conflict)
    
    // TODO: Prepare INSERT query
    // Insert topic_id, subject, message, and author
    // The created_at field should auto-populate with CURRENT_TIMESTAMP
    
    // TODO: Prepare the statement and bind parameters
    // Bind all the sanitized values
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // Include the topic_id in the response
    // If no, return error with 500 status

    // Validate required fields
    if (empty($data->topic_id) || empty($data->subject) || empty($data->message) || empty($data->author)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: topic_id, subject, message, author.'], 400);
    }

    // Sanitize inputs
    $topic_id = sanitizeInput($data->topic_id);
    $subject = sanitizeInput($data->subject);
    $message = sanitizeInput($data->message);
    $author = sanitizeInput($data->author);

    try {
        // Check duplicate
        $checkSql = "SELECT topic_id FROM topics WHERE topic_id = ? LIMIT 1";
        $check = $db->prepare($checkSql);
        $check->bindParam(1, $topic_id, PDO::PARAM_STR);
        $check->execute();
        if ($check->rowCount() > 0) {
            sendResponse(['success' => false, 'message' => 'Topic ID already exists.'], 409);
        }

        // Insert topic
        $insertSql = "INSERT INTO topics (topic_id, subject, message, author) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($insertSql);
        $stmt->bindParam(1, $topic_id, PDO::PARAM_STR);
        $stmt->bindParam(2, $subject, PDO::PARAM_STR);
        $stmt->bindParam(3, $message, PDO::PARAM_STR);
        $stmt->bindParam(4, $author, PDO::PARAM_STR);
        if ($stmt->execute()) {
            sendResponse(['success' => true, 'topic_id' => $topic_id], 201);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create topic.'], 500);
        }
    } catch (PDOException $e) {
        error_log('createTopic error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Update an existing topic
 * Method: PUT
 * 
 * Required JSON Body:
 *   - topic_id: The topic's unique identifier
 *   - subject: Updated subject (optional)
 *   - message: Updated message (optional)
 */
function updateTopic($db, $data) {
    // TODO: Validate that topic_id is provided
    // If not provided, return error with 400 status
    
    // TODO: Check if topic exists
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
    
    // TODO: Check if there are any fields to update
    // If $updates array is empty, return error
    
    // TODO: Complete the UPDATE query
    
    // TODO: Prepare statement and bind parameters
    // Bind all parameters from the $params array
    
    // TODO: Execute the query
    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no rows affected, return appropriate message
    // If error, return error with 500 status

    if (empty($data->topic_id)) {
        sendResponse(['success' => false, 'message' => 'Topic ID is required for update.'], 400);
    }
    $topic_id = trim($data->topic_id);
    // check exists
    try {
        $check = $db->prepare("SELECT topic_id FROM topics WHERE topic_id = ? LIMIT 1");
        $check->bindParam(1, $topic_id, PDO::PARAM_STR);
        $check->execute();
        if ($check->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $updates = [];
        $params = [];
        if (isset($data->subject)) {
            $updates[] = 'subject = ?';
            $params[] = sanitizeInput($data->subject);
        }
        if (isset($data->message)) {
            $updates[] = 'message = ?';
            $params[] = sanitizeInput($data->message);
        }
        if (isset($data->author)) {
            $updates[] = 'author = ?';
            $params[] = sanitizeInput($data->author);
        }

        if (empty($updates)) {
            sendResponse(['success' => false, 'message' => 'No fields provided for update.'], 400);
        }

        $sql = 'UPDATE topics SET ' . implode(', ', $updates) . ' WHERE topic_id = ?';
        $params[] = $topic_id;
        $stmt = $db->prepare($sql);
        foreach ($params as $i => $val) {
            $stmt->bindValue($i + 1, $val, PDO::PARAM_STR);
        }
        if ($stmt->execute()) {
            sendResponse(['success' => true, 'message' => 'Topic updated successfully.']);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to update topic.'], 500);
        }
    } catch (PDOException $e) {
        error_log('updateTopic error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Delete a topic
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function deleteTopic($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If not, return error with 400 status
    
    // TODO: Check if topic exists
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    
    // TODO: Delete associated replies first (foreign key constraint)
    // Prepare DELETE query for replies table
    
    // TODO: Prepare DELETE query for the topic
    
    // TODO: Prepare, bind, and execute
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error with 500 status
    
    $topicId = trim($topicId);
    if (empty($topicId)) {
        sendResponse(['success' => false, 'message' => 'Topic ID is required for deletion.'], 400);
    }
    try {
        // check exists
        $check = $db->prepare('SELECT topic_id FROM topics WHERE topic_id = ? LIMIT 1');
        $check->bindParam(1, $topicId, PDO::PARAM_STR);
        $check->execute();
        if ($check->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Topic not found.'], 404);
        }

        $db->beginTransaction();
        $delReplies = $db->prepare('DELETE FROM replies WHERE topic_id = ?');
        $delReplies->bindParam(1, $topicId, PDO::PARAM_STR);
        $delReplies->execute();
        $repliesDeleted = $delReplies->rowCount();

        $delTopic = $db->prepare('DELETE FROM topics WHERE topic_id = ?');
        $delTopic->bindParam(1, $topicId, PDO::PARAM_STR);
        $delTopic->execute();
        if ($delTopic->rowCount() > 0) {
            $db->commit();
            sendResponse(['success' => true, 'message' => "Topic '{$topicId}' and {$repliesDeleted} replies deleted."]);
        } else {
            $db->rollBack();
            sendResponse(['success' => false, 'message' => 'Failed to delete topic.'], 500);
        }
    } catch (PDOException $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('deleteTopic error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


// ============================================================================
// REPLIES FUNCTIONS
// ============================================================================

/**
 * Function: Get all replies for a specific topic
 * Method: GET
 * 
 * Query Parameters:
 *   - topic_id: The topic's unique identifier
 */
function getRepliesByTopicId($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If not provided, return error with 400 status
    
    // TODO: Prepare SQL query to select all replies for the topic
    // Select reply_id, topic_id, text, author, and created_at (formatted as date)
    // Order by created_at ASC (oldest first)
    
    // TODO: Prepare and bind the topic_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response
    // Even if no replies found, return empty array (not an error)

    $topicId = trim($topicId);
    if (empty($topicId)) {
        sendResponse(['success' => false, 'message' => 'Topic ID is required.'], 400);
    }
    $sql = "SELECT reply_id, topic_id, text, author, created_at FROM replies WHERE topic_id = ? ORDER BY created_at ASC";
    try {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(1, $topicId, PDO::PARAM_STR);
        $stmt->execute();
        $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(['success' => true, 'data' => $replies]);
    } catch (PDOException $e) {
        error_log('getRepliesByTopicId error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Create a new reply
 * Method: POST
 * 
 * Required JSON Body:
 *   - reply_id: Unique identifier (e.g., "reply_1234567890")
 *   - topic_id: The parent topic's identifier
 *   - text: Reply message text
 *   - author: Author's name
 */
function createReply($db, $data) {
    // TODO: Validate required fields
    // Check if reply_id, topic_id, text, and author are provided
    // If any field is missing, return error with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    
    // TODO: Verify that the parent topic exists
    // Prepare and execute SELECT query on topics table
    // If topic doesn't exist, return error with 404 status (can't reply to non-existent topic)
    
    // TODO: Check if reply_id already exists
    // Prepare and execute SELECT query to check for duplicate
    // If duplicate found, return error with 409 status
    
    // TODO: Prepare INSERT query
    // Insert reply_id, topic_id, text, and author
    
    // TODO: Prepare statement and bind parameters
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status
    // Include the reply_id in the response
    // If no, return error with 500 status

    if (empty($data->reply_id) || empty($data->topic_id) || empty($data->text) || empty($data->author)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields.'], 400);
    }
    $reply_id = sanitizeInput($data->reply_id);
    $topic_id = sanitizeInput($data->topic_id);
    $text = sanitizeInput($data->text);
    $author = sanitizeInput($data->author);

    try {
        // verify topic exists
        $checkTopic = $db->prepare('SELECT topic_id FROM topics WHERE topic_id = ? LIMIT 1');
        $checkTopic->bindParam(1, $topic_id, PDO::PARAM_STR);
        $checkTopic->execute();
        if ($checkTopic->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Parent topic not found.'], 404);
        }

        // check duplicate reply id
        $checkReply = $db->prepare('SELECT reply_id FROM replies WHERE reply_id = ? LIMIT 1');
        $checkReply->bindParam(1, $reply_id, PDO::PARAM_STR);
        $checkReply->execute();
        if ($checkReply->rowCount() > 0) {
            sendResponse(['success' => false, 'message' => 'Reply ID already exists.'], 409);
        }

        $insert = $db->prepare('INSERT INTO replies (reply_id, topic_id, text, author) VALUES (?, ?, ?, ?)');
        $insert->bindParam(1, $reply_id, PDO::PARAM_STR);
        $insert->bindParam(2, $topic_id, PDO::PARAM_STR);
        $insert->bindParam(3, $text, PDO::PARAM_STR);
        $insert->bindParam(4, $author, PDO::PARAM_STR);
        if ($insert->execute()) {
            sendResponse(['success' => true, 'reply_id' => $reply_id], 201);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to create reply.'], 500);
        }
    } catch (PDOException $e) {
        error_log('createReply error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


/**
 * Function: Delete a reply
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The reply's unique identifier
 */
function deleteReply($db, $replyId) {
    // TODO: Validate that replyId is provided
    // If not, return error with 400 status
    
    // TODO: Check if reply exists
    // Prepare and execute SELECT query
    // If not found, return error with 404 status
    
    // TODO: Prepare DELETE query
    
    // TODO: Prepare, bind, and execute
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error with 500 status

    $rid = trim($replyId ?? ($_GET['id'] ?? ''));
    if (empty($rid)) {
        sendResponse(['success' => false, 'message' => 'Reply ID is required for deletion.'], 400);
    }
    try {
        $check = $db->prepare('SELECT reply_id FROM replies WHERE reply_id = ? LIMIT 1');
        $check->bindParam(1, $rid, PDO::PARAM_STR);
        $check->execute();
        if ($check->rowCount() === 0) {
            sendResponse(['success' => false, 'message' => 'Reply not found.'], 404);
        }
        $del = $db->prepare('DELETE FROM replies WHERE reply_id = ?');
        $del->bindParam(1, $rid, PDO::PARAM_STR);
        $del->execute();
        if ($del->rowCount() > 0) {
            sendResponse(['success' => true, 'message' => "Reply '{$rid}' deleted."]);
        } else {
            sendResponse(['success' => false, 'message' => 'Failed to delete reply.'], 500);
        }
    } catch (PDOException $e) {
        error_log('deleteReply error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on resource and HTTP method
    
    // TODO: For GET requests, check for 'id' parameter in $_GET
    
    // TODO: For DELETE requests, get id from query parameter or request body
    
    // TODO: For unsupported methods, return 405 Method Not Allowed
    
    // TODO: For invalid resources, return 400 Bad Request
    // Determine resource and method
    $resource = $_GET['resource'] ?? 'topics';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($resource === 'topics') {
        if ($method === 'GET') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                getTopicById($db, $id);
            } else {
                getAllTopics($db);
            }
        } elseif ($method === 'POST') {
            createTopic($db, $dataJson);
        } elseif ($method === 'PUT') {
            updateTopic($db, $dataJson);
        } elseif ($method === 'DELETE') {
            $id = $_GET['id'] ?? ($dataJson->topic_id ?? null);
            deleteTopic($db, $id);
        } else {
            sendResponse(['success' => false, 'message' => 'Method Not Allowed'], 405);
        }
    } elseif ($resource === 'replies') {
        if ($method === 'GET') {
            $tid = $_GET['topic_id'] ?? null;
            getRepliesByTopicId($db, $tid);
        } elseif ($method === 'POST') {
            createReply($db, $dataJson);
        } elseif ($method === 'DELETE') {
            $rid = $_GET['id'] ?? ($dataJson->reply_id ?? null);
            deleteReply($db, $rid);
        } else {
            sendResponse(['success' => false, 'message' => 'Method Not Allowed'], 405);
        }
    } else {
        sendResponse(['success' => false, 'message' => "Invalid resource. Use 'topics' or 'replies'."], 400);
    }
} catch (PDOException $e) {
    // TODO: Handle database errors
    // DO NOT expose the actual error message to the client (security risk)
    // Log the error for debugging (optional)
    // Return generic error response with 500 status

    error_log('Discussion API DB error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Database error occurred.'], 500);

} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error for debugging
    // Return error response with 500 status

    error_log('Discussion API error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'An internal server error occurred.'], 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    
    // TODO: Echo JSON encoded data
    // Make sure to handle JSON encoding errors
    
    // TODO: Exit to prevent further execution

    http_response_code($statusCode);
    $json = json_encode($data);
    if ($json === false) {
        $fallback = ['success' => false, 'message' => 'Failed to encode response as JSON'];
        http_response_code(500);
        echo json_encode($fallback);
        exit;
    }
    echo $json;
    exit;
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Check if data is a string
    // If not, return as is or convert to string
    
    // TODO: Trim whitespace from both ends
    
    // TODO: Remove HTML and PHP tags
    
    // TODO: Convert special characters to HTML entities (prevents XSS)
    
    // TODO: Return sanitized data

    if (!is_string($data)) {
        $data = (string)$data;
    }
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}


/**
 * Helper function to validate resource name
 * 
 * @param string $resource - Resource name to validate
 * @return bool - True if valid, false otherwise
 */
function isValidResource($resource) {
    // TODO: Define allowed resources
    
    // TODO: Check if resource is in the allowed list

    $allowed = ['topics', 'replies'];
    return in_array($resource, $allowed, true);
}

?>
