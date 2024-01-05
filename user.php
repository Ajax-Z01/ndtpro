<?php
require_once 'auth.php';

function getLoggedInUserData()
{

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        $conn = connectToDatabase();

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);

        $stmt->execute();

        $result = $stmt->get_result();

        $stmt->close();
        $conn->close();

        if ($user = $result->fetch_assoc()) {
            return $user;
        }
    }

    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        updateProfile();
    }

    if (isset($_POST['update_picture'])) {
        uploadProfilePicture();
    }

    if (isset($_POST['delete_picture'])) {
        deleteProfilePicture();
    }
}

function updateProfile()
{
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $company = $_POST['company'];
    $qualifications = $_POST['qualifications'];
    $phone_number = $_POST['phone_number'];
    $certificate_number = $_POST['certificate_number'];
    $certificate_issue_date = $_POST['certificate_issue_date'];
    $certificate_validity_period_until_date = $_POST['certificate_validity_period_until_date'];

    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, company=?, qualifications=?, phone_number=?, certificate_number=?, certificate_issue_date=?, certificate_validity_period_until_date=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $full_name, $username, $email, $company, $qualifications, $phone_number, $certificate_number, $certificate_issue_date, $certificate_validity_period_until_date, $_SESSION['user_id']);

    $stmt->execute();

    $stmt->close();
    $conn->close();

    header("Location: profile.php");
    exit;
}

function uploadProfilePicture()
{
    $user_id = $_SESSION['user_id'];

    $uploadDir = 'uploads/';

    $uploadedFile = $_FILES['profile_picture'];
    $fileName = $uploadedFile['name'];
    $fileTmpName = $uploadedFile['tmp_name'];
    $fileSize = $uploadedFile['size'];
    $fileError = $uploadedFile['error'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxFileSize = 800 * 1024;

    if ($fileError === UPLOAD_ERR_OK) {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            echo "Invalid file format. Allowed formats: " . implode(', ', $allowedExtensions);
            exit;
        }

        if ($fileSize > $maxFileSize) {
            echo "File size exceeds the limit. Maximum size: " . ($maxFileSize / 1024) . "KB";
            exit;
        }

        $newFileName = 'profile_picture_' . $user_id . '.' . $fileExtension;

        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            updateUserImage($user_id, $uploadPath);

            header("Location: profile.php");
            exit;
        } else {
            echo "Failed to move the uploaded file.";
        }
    } else {
        echo "Error uploading file. Error code: $fileError";
    }
}

function updateUserImage($user_id, $newImagePath)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE users SET image=? WHERE id=?");
    $stmt->bind_param("si", $newImagePath, $user_id);

    $stmt->execute();

    $stmt->close();
    $conn->close();
}


function deleteProfilePicture()
{
    $user_id = $_SESSION['user_id'];

    $currentImagePath = getCurrentUserImagePath($user_id);
    $defaultImagePath = 'assets/img/default-profile.webp';

    if ($currentImagePath !== $defaultImagePath && file_exists($currentImagePath)) {
        unlink($currentImagePath);
    }

    updateUserImage($user_id, $defaultImagePath);

    header("Location: profile.php");
    exit;
}


function getCurrentUserImagePath($user_id)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT image FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();

    $stmt->close();
    $conn->close();

    return $imagePath;
}
