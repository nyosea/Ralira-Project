<!DOCTYPE html>
<html>
<head>
    <title>Download Test Result - Debug</title>
</head>
<body>
    <h1>Download Test Result - Debug</h1>
    
    <p>User yang login sekarang harus memiliki test result dengan ID 1, 3, 4, atau 6</p>
    
    <h2>Direct API Test:</h2>
    <ul>
        <li><a href="api/download_test_result_new.php?id=1" target="_blank">Download Test Result ID 1</a></li>
        <li><a href="api/download_test_result_new.php?id=3" target="_blank">Download Test Result ID 3</a></li>
        <li><a href="api/download_test_result_new.php?id=4" target="_blank">Download Test Result ID 4</a></li>
        <li><a href="api/download_test_result_new.php?id=6" target="_blank">Download Test Result ID 6</a></li>
    </ul>
    
    <h2>Session Info:</h2>
    <pre><?php 
    session_start();
    echo "user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
    echo "role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
    echo "Full Session: " . json_encode($_SESSION, JSON_PRETTY_PRINT);
    ?></pre>
</body>
</html>
