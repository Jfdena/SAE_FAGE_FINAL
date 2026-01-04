<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 3);
require_once $docRoot . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

function connectToDatabase($mysqli, $username, $password)
{
    $stmt = $mysqli->prepare("SELECT password, id_user, role FROM Users WHERE username = ?");
    if (!$stmt) {
        return ['success' => false, 'error' => 'Database error'];
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashed_password_from_db = $row['password'];

        if (password_verify($password, $hashed_password_from_db)) {
            return [
                'success' => true,
                'role' => $row['role'],
                'ref_user' => $row['Ref_user']
            ];
        } else {
            // Incorrect password
            return ['success' => false, 'error' => 'Incorrect password'];
        }
    } else {
        // User not found
        return ['success' => false, 'error' => 'User not found'];
    }
}