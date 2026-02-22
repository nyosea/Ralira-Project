<?php
/**
 * Test JSON Response - Simulate the exact same flow
 */

// Start session (same as main file)
session_start();

// Clear any output buffer at the start
if (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffer
ob_start();

// Simulate some HTML output (this might be the problem)
echo "<!-- Some HTML comment that might cause issues -->";

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Get current output buffer content
    $buffer_content = ob_get_contents();
    
    // Clean buffer completely
    ob_end_clean();
    
    // Check for any unwanted output
    if (!empty($buffer_content)) {
        echo "Buffer content found: " . var_export($buffer_content, true) . "\n";
        echo "Length: " . strlen($buffer_content) . "\n";
        echo "Position 79 character: '" . substr($buffer_content, 78, 1) . "'\n";
        exit;
    }
    
    // Set JSON header
    header('Content-Type: application/json');
    
    // Disable any further output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if ($_POST['action'] === 'test') {
        echo json_encode(['success' => true, 'message' => 'Test successful']);
        exit;
    }
}

// Normal HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <title>JSON Test</title>
</head>
<body>
    <form method="POST">
        <input type="hidden" name="action" value="test">
        <button type="submit">Test JSON</button>
    </form>
</body>
</html>
