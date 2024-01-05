<?php
require_once 'auth.php';

function createDocumentation($user_id, $title, $description, $imagePath)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("INSERT INTO documentation (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $imagePath);

    $stmt->execute();

    $stmt->close();
    $conn->close();
}

function getDocumentationData($user_id)
{
    $conn = connectToDatabase();

    $sql = "SELECT id, user_id, title, description, image_path, uploaded_at FROM documentation WHERE user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $documentationData = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $documentationData[] = $row;
        }
    }

    $stmt->close();
    $conn->close();
    return $documentationData;
}


function updateDocumentation($id, $user_id, $title, $description, $imagePath)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE documentation SET user_id=?, title=?, description=?, image_path=? WHERE id=?");
    $stmt->bind_param("isssi", $user_id, $title, $description, $imagePath, $id);

    $stmt->execute();

    $stmt->close();
    $conn->close();
}

function deleteDocumentation($id)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT image_path FROM documentation WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    if ($imagePath) {
        unlink($imagePath);
    }

    $stmt = $conn->prepare("DELETE FROM documentation WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $uploadDir = 'uploads/';
    $uploadedFile = $_FILES['image'];
    $fileName = $uploadedFile['name'];
    $fileTmpName = $uploadedFile['tmp_name'];
    $fileSize = $uploadedFile['size'];
    $fileError = $uploadedFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxFileSize = 800 * 1024;

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        $_SESSION['message'] = 'File type not allowed.';
        header("Location: documentation.php");
        exit;
    }

    if ($fileSize > $maxFileSize) {
        $_SESSION['message'] = 'File size exceeds the maximum limit.';
        header("Location: documentation.php");
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newFileName = uniqid('image_') . '_' . $fileName;
    $imagePath = $uploadDir . $newFileName;

    move_uploaded_file($fileTmpName, $imagePath);

    createDocumentation($user_id, $title, $description, $imagePath);
    $_SESSION['message'] = 'Picture uploaded successfully.';
    header("Location: documentation.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $user_id = $_SESSION['user_id'];
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];

    if (!isset($_FILES['image']['name'])) {
        $uploadPath = $_POST['current_image_path'];
    } else {
        $uploadDir = 'uploads/';
        $uploadedFile = $_FILES['image'];
        $fileName = $uploadedFile['name'];
        $fileTmpName = $uploadedFile['tmp_name'];

        $newFileName = uniqid('image_') . '_' . $fileName;
        $uploadPath = $uploadDir . $newFileName;

        move_uploaded_file($fileTmpName, $uploadPath);
    }

    updateDocumentation($id, $user_id, $title, $description, $imagePath);
    $_SESSION['message'] = 'Picture updated successfully.';
    header("Location: documentation.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    deleteDocumentation($id);
    $_SESSION['message'] = 'Picture deleted successfully.';
    header("Location: documentation.php");
    exit;
}
