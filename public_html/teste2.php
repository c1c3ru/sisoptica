<?php
// Database credentials (replace with your actual values)
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_name = 'u293830981_os';

// Use recommended mysqli instead of deprecated mysql_* functions
try {
    // Connect to the database
    $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error;
        exit;
    }

    // Prepare and execute the query (assuming the correct column name)
    $sql = "SELECT * FROM funcionario";
    $result = $conn->query($sql);

    if (!$result) {
        echo "DB Error: " . $conn->error;
        exit;
    }

    // Fetch and display data iteratively
    while ($row = $result->fetch_assoc()) {
        // Replace "column_name" with the actual column you want to display
        echo $row["nome_funcionario"] . "<br>";  // Or use other formatting
    }

    // Close the result and connection
    $result->close();
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
