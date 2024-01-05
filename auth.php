<?php
session_start();

function connectToDatabase()
{
    $host = 'sql301.infinityfree.com';
    $user = 'if0_35611058';
    $password = 'ybYUOSCTasMpUN';
    $database = 'if0_35611058_ndtpro';

    $conn = new mysqli($host, $user, $password, $database);

    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }

    return $conn;
}

function registerUser($full_name, $username, $email, $password)
{
    $conn = connectToDatabase();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $username, $email, $hashedPassword);

    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function authenticateUser($username, $password)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT id, full_name, username, email, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);

    $stmt->execute();
    $stmt->bind_result($userId, $fullName, $dbUsername, $email, $dbPassword);
    $stmt->fetch();
    $stmt->close();

    $conn->close();

    if ($dbUsername && password_verify($password, $dbPassword)) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['full_name'] = $fullName;
        $_SESSION['email'] = $email;
        return true;
    } else {
        return false;
    }
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function logout()
{
    session_destroy();
    header("Location: index.php");
    exit;
}

function closeDatabaseConnection($conn)
{
    $conn->close();
}
