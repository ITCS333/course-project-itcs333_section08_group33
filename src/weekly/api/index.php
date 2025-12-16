<?php
session_start();

$_SESSION['dummy'] = true;
/**
 * Weekly Course Breakdown API
 * 
 * This is a RESTful API that handles all CRUD operations for weekly course content
 * and discussion comments. It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: weeks
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50), UNIQUE) - Unique identifier (e.g., "week_1")
 *   - title (VARCHAR(200))
 *   - start_date (DATE)
 *   - description (TEXT)
 *   - links (TEXT) - JSON encoded array of links
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50)) - Foreign key reference to weeks.week_id
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve week(s) or comment(s)
 *   - POST: Create a new week or comment
 *   - PUT: Update an existing week
 *   - DELETE: Delete a week or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// SETUP AND CONFIGURATION
// ============================================================================

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
header('Content-Type: application/json');
// Allow cross-origin requests (CORS) if needed
header('Access-Control-Allow-Origin: *');
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// Allow specific headers (Content-Type, Authorization)
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
// Example: require_once '../config/Database.php';
require_once __DIR__ . '/../../config/Database.php';
// TODO: Get the PDO database connection
// Example: $database = new Database();
//          $db = $database->getConnection();
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (PDOException $e) {
    sendError("Database connection failed.", 500);
}

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$rawJson = file_get_contents('php://input');
$dataJson = json_decode($rawJson);

if (in_array($method, ['POST', 'PUT']) && $rawJson === '') {
    sendError("Missing JSON body.", 400);
}

if (in_array($method, ['POST', 'PUT']) && json_last_error() !== JSON_ERROR_NONE) {
    sendError("Invalid JSON body.", 400);
}

// TODO: Parse query parameters
// Get the 'resource' parameter to determine if request is for weeks or comments
// Example: ?resource=weeks or ?resource=comments
$resource = $_GET['resource'] ?? null;

// ============================================================================
// WEEKS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all weeks or search for specific weeks
 * Method: GET
 * Resource: weeks
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, start_date)
 *   - order: Optional sort order (asc or desc, default: asc)
 */
function getAllWeeks($db)
{
    // TODO: Initialize variables for search, sort, and order from query parameters
    $searchTerm = $_GET['search'] ?? null;
    $sortBy = $_GET['sort'] ?? 'start_date';
    $order = $_GET['order'] ?? 'asc';
    $allowedSortFields = ['title', 'start_date', 'created_at'];

    // TODO: Start building the SQL query
    // Base query: SELECT week_id, title, start_date, description, links, created_at FROM weeks
    $query = "SELECT id, title, start_date, description, links, created_at, updated_at FROM weeks";
    $params = [];
    // TODO: Check if search parameter exists
    // If yes, add WHERE clause using LIKE for title and description
    // Example: WHERE title LIKE ? OR description LIKE ?
    if ($searchTerm) {
        $query .= " WHERE title LIKE ? OR description LIKE ?";
        // PDO requires the full string with wildcards for binding
        $params[] = "%" . $searchTerm . "%";
        $params[] = "%" . $searchTerm . "%";
    }

    // TODO: Check if sort parameter exists
    // Validate sort field to prevent SQL injection (only allow: title, start_date, created_at)
    // If invalid, use default sort field (start_date)
    if (!isValidSortField($sortBy, $allowedSortFields)) {
        $sortBy = 'start_date';
    }
    // TODO: Check if order parameter exists
    // Validate order to prevent SQL injection (only allow: asc, desc)
    // If invalid, use default order (asc)
    $order = strtolower($order);

    if ($order === 'desc') {
        $order = 'DESC';
    } else {
        $order = 'ASC';
    }


    // TODO: Add ORDER BY clause to the query
    $query .= " ORDER BY " . $sortBy . " " . $order;
    // TODO: Prepare the SQL query using PDO
    try {
        
        // TODO: Bind parameters if using search
        // Use wildcards for LIKE: "%{$searchTerm}%"
        $stmt = $db->prepare($query);
        // TODO: Execute the query
        $stmt->execute($params);
        // TODO: Fetch all results as an associative array
        $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // TODO: Process each week's links field
        // Decode the JSON string back to an array using json_decode()
        foreach ($weeks as &$week) {
            $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
        }

        // TODO: Return JSON response with success status and data
        // Use sendResponse() helper function
        sendResponse(['success' => true, 'data' => $weeks]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}


/**
 * Function: Get a single week by week_id
 * Method: GET
 * Resource: weeks
 * 
 * Query Parameters:
 *   - week_id: The unique week identifier (e.g., "week_1")
 */
function getWeekById($db, $weekId)
{
    if (empty($weekId) || !is_numeric($weekId)) {
        sendError("Week ID is required.", 400);
    }

    $weekId = (int)$weekId;

    $query = "SELECT id, title, start_date, description, links, created_at, updated_at
              FROM weeks
              WHERE id = ?
              LIMIT 1";

    try {
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $weekId, PDO::PARAM_INT);
        $stmt->execute();

        $week = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$week) {
            sendError("Week not found.", 404);
        }

        $week['links'] = $week['links']
            ? json_decode($week['links'], true)
            : [];

        sendResponse(['success' => true, 'data' => $week]);
    } catch (PDOException $e) {
        error_log("getWeekById ERROR: " . $e->getMessage());
        sendError("get week by id database statement failed.", 500);
    }
}



/**
 * Function: Create a new week
 * Method: POST
 * Resource: weeks
 * 
 * Required JSON Body:
 *   - week_id: Unique week identifier (e.g., "week_1")
 *   - title: Week title (e.g., "Week 1: Introduction to HTML")
 *   - start_date: Start date in YYYY-MM-DD format
 *   - description: Week description
 *   - links: Array of resource links (will be JSON encoded)
 */
function createWeek($db, $data)
{
    // TODO: Validate required fields
    // Check if week_id, title, start_date, and description are provided
    // If any field is missing, return error response with 400 status
    if (empty($data->week_id) || empty($data->title) || empty($data->start_date) || empty($data->description)) {
        sendError("Missing required fields: week_id, title, start_date, and description.", 400);
    }

    // TODO: Sanitize input data
    // Trim whitespace from title, description, and week_id
    $week_id = sanitizeInput($data->week_id);
    $title = sanitizeInput($data->title);
    $description = sanitizeInput($data->description);
    $start_date = trim($data->start_date);

    // TODO: Validate start_date format
    // Use a regex or DateTime::createFromFormat() to verify YYYY-MM-DD format
    // If invalid, return error response with 400 status
    if (!validateDate($start_date)) {
        sendError("Invalid start_date format. Must be YYYY-MM-DD.", 400);
    }
    // TODO: Check if week_id already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    $checkQuery = "SELECT id FROM weeks WHERE id = ? LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $week_id, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->rowCount() > 0) {
        sendError("Week with ID '{$week_id}' already exists.", 409); // 409 Conflict
    }

    // TODO: Handle links array
    // If links is provided and is an array, encode it to JSON using json_encode()
    // If links is not provided, use an empty array []
    $linksArray = $data->links ?? [];
    if (!is_array($linksArray)) {
        sendError("Links must be provided as an array.", 400);
    }
    $linksJson = json_encode($linksArray);

    // TODO: Prepare INSERT query
    // INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)
    $query = "INSERT INTO weeks (id, title, start_date, description, links) 
              VALUES (?, ?, ?, ?, ?)";

    try {
        $stmt = $db->prepare($query);
        // TODO: Bind parameters
        $stmt->bindParam(1, $week_id, PDO::PARAM_STR);
        $stmt->bindParam(2, $title, PDO::PARAM_STR);
        $stmt->bindParam(3, $start_date, PDO::PARAM_STR);
        $stmt->bindParam(4, $description, PDO::PARAM_STR);
        $stmt->bindParam(5, $linksJson, PDO::PARAM_STR);
        // TODO: Execute the query
        if ($stmt->execute()) {
            // TODO: Check if insert was successful
            // If yes, return success response with 201 status (Created) and the new week data
            // If no, return error response with 500 status
            $newWeek = [
                'id' => $week_id,
                'title' => $title,
                'start_date' => $start_date,
                'description' => $description,
                'links' => $linksArray,
                'created_at' => date('Y-m-d H:i:s'), // Mock timestamp for response
            ];
            sendResponse(['success' => true, 'data' => $newWeek], 201);
        } else {
            sendError("Failed to create week. Database statement failed.", 500);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        sendError("create week database statement failed.", 500);
    }
}


/**
 * Function: Update an existing week
 * Method: PUT
 * Resource: weeks
 * 
 * Required JSON Body:
 *   - week_id: The week identifier (to identify which week to update)
 *   - title: Updated week title (optional)
 *   - start_date: Updated start date (optional)
 *   - description: Updated description (optional)
 *   - links: Updated array of links (optional)
 */
function updateWeek($db, $data)
{
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (empty($data->week_id)) {
        sendError("Week ID is required for update.", 400);
    }
    $week_id = trim($data->week_id);

    // TODO: Check if week exists
    // Prepare and execute a SELECT query to find the week
    // If not found, return error response with 404 status
    $existingWeek = null;
    try {
        $checkQuery = "SELECT id FROM weeks WHERE id = ? LIMIT 1";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(1, $week_id, PDO::PARAM_STR);
        $checkStmt->execute();
        if ($checkStmt->rowCount() === 0) {
            sendError("Week not found for ID: {$week_id}", 404);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }

    // TODO: Build UPDATE query dynamically based on provided fields
    // Initialize an array to hold SET clauses
    // Initialize an array to hold values for binding
    $setClauses = [];
    $bindValues = [];

    // TODO: Check which fields are provided and add to SET clauses
    // If title is provided, add "title = ?"
    // If start_date is provided, validate format and add "start_date = ?"
    // If description is provided, add "description = ?"
    // If links is provided, encode to JSON and add "links = ?"
    $fields = [
        'title' => 'title',
        'description' => 'description'
    ];
    foreach ($fields as $prop => $col) {
        if (isset($data->$prop)) {
            $setClauses[] = "{$col} = ?";
            $bindValues[] = sanitizeInput($data->$prop);
        }
    }
    // TODO: If no fields to update, return error response with 400 status
    if (isset($data->start_date)) {
        $date = trim($data->start_date);
        if (!validateDate($date)) {
            sendError("Invalid start_date format. Must be YYYY-MM-DD.", 400);
        }
        $setClauses[] = "start_date = ?";
        $bindValues[] = $date;
    }
    // TODO: Add updated_at timestamp to SET clauses
    // Add "updated_at = CURRENT_TIMESTAMP"
    if (isset($data->links)) {
        if (!is_array($data->links)) {
            sendError("Links must be provided as an array.", 400);
        }
        $setClauses[] = "links = ?";
        $bindValues[] = json_encode($data->links);
    }
    // TODO: Build the complete UPDATE query
    // UPDATE weeks SET [clauses] WHERE week_id = ?
    if (empty($setClauses)) {
        sendError("No fields provided for update.", 400);
    }
    // TODO: Prepare the query
    $setClauses[] = "updated_at = CURRENT_TIMESTAMP";
    // TODO: Bind parameters dynamically
    // Bind values array and then bind week_id at the end
    $query = "UPDATE weeks SET " . implode(', ', $setClauses) . " WHERE id = ?";
    // TODO: Execute the query
    try {
        $stmt = $db->prepare($query);
        for ($i = 0; $i < count($bindValues); $i++) {
            // Parameter index starts at 1, so $i + 1
            $stmt->bindValue($i + 1, $bindValues[$i], PDO::PARAM_STR);
        }

        $stmt->bindValue(count($bindValues) + 1, $week_id, PDO::PARAM_STR);

        $stmt->execute();
        // TODO: Check if update was successful
        // If yes, return success response with updated week data
        // If no, return error response with 500 status
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            sendResponse([
                'success' => true,
                'message' => "No changes made.",
                'week_id' => $week_id
            ]);
        } else {
            getWeekById($db, $week_id);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        sendError("update week database statement failed.", 500);
    }
}


/**
 * Function: Delete a week
 * Method: DELETE
 * Resource: weeks
 * 
 * Query Parameters or JSON Body:
 *   - week_id: The week identifier
 */
function deleteWeek($db, $weekId)
{
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    $weekId = trim($weekId);
    if (empty($weekId)) {
        sendError("Week ID is required for deletion.", 400);
    }
    // TODO: Check if week exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $checkQuery = "SELECT id FROM weeks WHERE id = ? LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $weekId, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->rowCount() === 0) {
        sendError("Week not found for ID: {$weekId}", 404);
    }

    try {
        $db->beginTransaction();
        // TODO: Delete associated comments first (to maintain referential integrity)
        // Prepare DELETE query for comments table
        // DELETE FROM comments WHERE week_id = ?
        $deleteCommentsQuery = "DELETE FROM comments_week WHERE id = ?";
        $commentsStmt = $db->prepare($deleteCommentsQuery);
        $commentsStmt->bindParam(1, $weekId, PDO::PARAM_STR);
        $commentsStmt->execute();
        $commentsDeletedCount = $commentsStmt->rowCount();
        // TODO: Execute comment deletion query

        // TODO: Prepare DELETE query for week
        // DELETE FROM weeks WHERE week_id = ?
        $deleteWeekQuery = "DELETE FROM weeks WHERE id = ?";
        $weekStmt = $db->prepare($deleteWeekQuery);
        // TODO: Bind the week_id parameter
        $weekStmt->bindParam(1, $weekId, PDO::PARAM_STR);
        // TODO: Execute the query
        // TODO: Check if delete was successful
        // If yes, return success response with message indicating week and comments deleted
        // If no, return error response with 500 status
        if ($weekStmt->execute() && $weekStmt->rowCount() > 0) {
            $db->commit();
            sendResponse([
                'success' => true,
                'message' => "Week '{$weekId}' and {$commentsDeletedCount} associated comments deleted successfully."
            ]);
        } else {
            $db->rollBack();
            sendError("Failed to delete week. Week may not exist.", 500);
        }
    } catch (PDOException $e) {
        $db->rollBack();
        error_log($e->getMessage());
        sendError("delete week database statement failed.", 500);
    }
}


// ============================================================================
// COMMENTS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all comments for a specific week
 * Method: GET
 * Resource: comments
 * 
 * Query Parameters:
 *   - week_id: The week identifier to get comments for
 */
function getCommentsByWeek($db, $weekId)
{
    if (empty($weekId) || !is_numeric($weekId)) {
        sendError("Week ID is required to fetch comments.", 400);
    }

    $weekId = (int)$weekId;

    $query = "SELECT id, week_id, author, text, created_at
              FROM comments_week
              WHERE week_id = ?
              ORDER BY created_at ASC";

    try {
        $stmt = $db->prepare($query);
        $stmt->bindValue(1, $weekId, PDO::PARAM_INT);
        $stmt->execute();

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(['success' => true, 'data' => $comments]);
    } catch (PDOException $e) {
            error_log($e->getMessage());
            sendError($e->getMessage(), 500);
        
        error_log("getCommentsByWeek ERROR: " . $e->getMessage());
        sendError("get comment database statement failed.", 500);
    }
}



/**
 * Function: Create a new comment
 * Method: POST
 * Resource: comments
 * 
 * Required JSON Body:
 *   - week_id: The week identifier this comment belongs to
 *   - author: Comment author name
 *   - text: Comment text content
 */
function createComment($db, $data)
{
    // TODO: Validate required fields
    // Check if week_id, author, and text are provided
    // If any field is missing, return error response with 400 status
    if (empty($data->week_id) || empty($data->author) || empty($data->text)) {
        sendError("Missing required fields: week_id, author, and text.", 400);
    }
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    $week_id = sanitizeInput($data->week_id);
    $author = sanitizeInput($data->author);
    $text = sanitizeInput($data->text);
    // TODO: Validate that text is not empty after trimming
    // If empty, return error response with 400 status
    if (empty($text)) {
        sendError("Comment text cannot be empty.", 400);
    }
    // TODO: Check if the week exists
    // Prepare and execute a SELECT query on weeks table
    // If week not found, return error response with 404 status
    $checkQuery = "SELECT id FROM weeks WHERE id = ? LIMIT 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(1, $week_id, PDO::PARAM_STR);
    $checkStmt->execute();
    if ($checkStmt->rowCount() === 0) {
        sendError("Cannot create comment. Week '{$week_id}' not found.", 404);
    }
    // TODO: Prepare INSERT query
    // INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)
    $query = "INSERT INTO comments_week (week_id, author, text) VALUES (?, ?, ?)";

    try {
        $stmt = $db->prepare($query);
        // TODO: Bind parameters
        $stmt->bindParam(1, $week_id, PDO::PARAM_STR);
        $stmt->bindParam(2, $author, PDO::PARAM_STR);
        $stmt->bindParam(3, $text, PDO::PARAM_STR);
        // TODO: Execute the query
        if ($stmt->execute()) {
            // TODO: Check if insert was successful
            // If yes, get the last insert ID and return success response with 201 status
            // Include the new comment data in the response
            // If no, return error response with 500 status
            $newId = $db->lastInsertId();
            $newComment = [
                'id' => (int)$newId,
                'week_id' => $week_id,
                'author' => $author,
                'text' => $text,
                'created_at' => date('Y-m-d H:i:s'), // Mock timestamp for response
            ];
            sendResponse(['success' => true, 'data' => $newComment], 201);
        } else {
            sendError("Failed to create comment. Database statement failed.", 500);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        sendError("create comment database statement failed.", 500);
    }
}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Resource: comments
 * 
 * Query Parameters or JSON Body:
 *   - id: The comment ID to delete
 */
function deleteComment($db, $commentId)
{
    // TODO: Validate that id is provided
    // If not, return error response with 400 status
    if (empty($commentId) || !is_numeric($commentId)) {
        sendError("Comment ID is required for deletion.", 400);
    }

    $commentId = (int)$commentId;

    // TODO: Check if comment exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status


    try {
        // TODO: Prepare DELETE query
        // DELETE FROM comments WHERE id = ?
        $query = "DELETE FROM comments_week WHERE id = ?";
        $stmt = $db->prepare($query);
        // TODO: Bind the id parameter
        $stmt->bindParam(1, $commentId, PDO::PARAM_INT);
        // TODO: Execute the query
        $stmt->execute();
        // TODO: Check if delete was successful
        // If yes, return success response
        // If no, return error response with 500 status
        if ($stmt->rowCount() > 0) {
            sendResponse(['success' => true, 'message' => "Comment ID {$commentId} deleted successfully."]);
        } else {
            $checkQuery = "SELECT id FROM comments_week WHERE id = ? LIMIT 1";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(1, $commentId, PDO::PARAM_INT);
            $checkStmt->execute();
            if ($checkStmt->rowCount() === 0) {
                sendError("Comment not found for ID: {$commentId}", 404);
            } else {
                sendError("Failed to delete comment. Database statement failed or comment already deleted.", 500);
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        sendError("delete comment database statement failed.", 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Determine the resource type from query parameters
    // Get 'resource' parameter (?resource=weeks or ?resource=comments)
    // If not provided, default to 'weeks'
    $weekId = $_GET['id'] ?? null;
    $commentId = $_GET['id'] ?? null;
    if (empty($weekId) && isset($dataJson->week_id)) {
        $weekId = $dataJson->id;
    }
    if (empty($commentId) && isset($dataJson->id)) {
        $commentId = $dataJson->id;
    }
    // Route based on resource type and HTTP method
    try {
        $resource = $_GET['resource'] ?? 'weeks';
        // ========== WEEKS ROUTES ==========
        if ($resource === 'weeks') {

            if ($method === 'GET') {
                // TODO: Check if week_id is provided in query parameters
                // If yes, call getWeekById()
                // If no, call getAllWeeks() to get all weeks (with optional search/sort)
                if ($weekId) {
                    getWeekById($db, $weekId);
                } else {
                    getAllWeeks($db);
                }
            } elseif ($method === 'POST') {
                // TODO: Call createWeek() with the decoded request body
                createWeek($db, $dataJson);
            } elseif ($method === 'PUT') {
                // TODO: Call updateWeek() with the decoded request body
                updateWeek($db, $dataJson);
            } elseif ($method === 'DELETE') {
                // TODO: Get week_id from query parameter or request body
                // Call deleteWeek()
                deleteWeek($db, $weekId);
            } else {
                // TODO: Return error for unsupported methods
                // Set HTTP status to 405 (Method Not Allowed)
                sendError("Method Not Allowed", 405);
            }
        }

        // ========== COMMENTS ROUTES ==========
        elseif ($resource === 'comments') {

            if ($method === 'GET') {
                // TODO: Get week_id from query parameters
                // Call getCommentsByWeek()
                getCommentsByWeek($db, $weekId);
            } elseif ($method === 'POST') {
                // TODO: Call createComment() with the decoded request body
                createComment($db, $dataJson);
            } elseif ($method === 'DELETE') {
                // TODO: Get comment id from query parameter or request body
                // Call deleteComment()
                deleteComment($db, $commentId);
            } else {
                // TODO: Return error for unsupported methods
                // Set HTTP status to 405 (Method Not Allowed)
                sendError("Method Not Allowed", 405);
            }
        }

        // ========== INVALID RESOURCE ==========
        else {
            // TODO: Return error for invalid resource
            // Set HTTP status to 400 (Bad Request)
            // Return JSON error message: "Invalid resource. Use 'weeks' or 'comments'"
            sendError("Invalid resource. Use 'weeks' or 'comments'", 400);
        }
    } catch (PDOException $e) {
        // TODO: Handle database errors
        // Log the error message (optional, for debugging)
        // error_log($e->getMessage());
        error_log("PDO ERROR: " . $e->getMessage());
        // TODO: Return generic error response with 500 status
        // Do NOT expose database error details to the client
        // Return message: "Database error occurred"  
        sendError("Database error occurred.", 500);
    }
} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error message (optional)
    // Return error response with 500 status
    error_log("GENERAL ERROR: " . $e->getMessage());
    sendError("server error occurred.", 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200)
{
    // TODO: Set HTTP response code
    // Use http_response_code($statusCode)
    http_response_code($statusCode);
    // TODO: Echo JSON encoded data
    // Use json_encode($data)
    echo json_encode($data);
    // TODO: Exit to prevent further execution
    exit;
}


/**
 * Helper function to send error response
 * 
 * @param string $message - Error message
 * @param int $statusCode - HTTP status code
 */
function sendError($message, $statusCode = 400)
{
    // TODO: Create error response array
    // Structure: ['success' => false, 'error' => $message]
    $response = ['success' => false, 'error' => $message];
    // TODO: Call sendResponse() with the error array and status code
    sendResponse($response, $statusCode);
}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date)
{
    // TODO: Use DateTime::createFromFormat() to validate
    // Format: 'Y-m-d'
    $d = DateTime::createFromFormat('Y-m-d', $date);
    // Check that the created date matches the input string
    // Return true if valid, false otherwise
    if ($d && $d->format('Y-m-d') === $date) {
        return true;
    } else {
        return false;
    }
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data)
{
    // TODO: Trim whitespace
    $data = trim($data);
    // TODO: Strip HTML tags using strip_tags()
    $data = strip_tags($data);
    // TODO: Convert special characters using htmlspecialchars()
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // TODO: Return sanitized data
    return $data;
}


/**
 * Helper function to validate allowed sort fields
 * 
 * @param string $field - Field name to validate
 * @param array $allowedFields - Array of allowed field names
 * @return bool - True if valid, false otherwise
 */
function isValidSortField($field, $allowedFields)
{
    // TODO: Check if $field exists in $allowedFields array
    // Use in_array()
    // Return true if valid, false otherwise
    return in_array($field, $allowedFields);
}
