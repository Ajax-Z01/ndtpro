<?php
require_once 'auth.php';

function createWorkActivity($user_id, $activity_name, $date, $location, $employer_organization, $company_field_coordinator, $status)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("INSERT INTO work_activities (user_id, activity_name, date, location, employer_organization, company_field_coordinator, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $user_id, $activity_name, $date, $location, $employer_organization, $company_field_coordinator, $status);

    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function getWorkActivities($user_id)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("SELECT * FROM work_activities WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    $stmt->execute();
    $result = $stmt->get_result();
    $workActivities = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    $conn->close();

    return $workActivities;
}

function updateWorkActivity($id, $activity_name, $date, $location, $employer_organization, $company_field_coordinator, $status)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("UPDATE work_activities SET activity_name=?, date=?, location=?, employer_organization=?, company_field_coordinator=?, status=? WHERE id=?");
    $stmt->bind_param("ssssssi", $activity_name, $date, $location, $employer_organization, $company_field_coordinator, $status, $id);

    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function deleteWorkActivity($id)
{
    $conn = connectToDatabase();

    $stmt = $conn->prepare("DELETE FROM work_activities WHERE id=?");
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function countProducts($search_query = '')
{
    $conn = connectToDatabase();

    $escaped_search_query = mysqli_real_escape_string($conn, $search_query);

    $sql = "SELECT COUNT(*) as total FROM work_activities";
    if (!empty($escaped_search_query)) {
        $sql .= " WHERE activity_name LIKE '%$escaped_search_query%'";
    }

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    closeDatabaseConnection($conn);

    return $row['total'];
}

// For Dashboard
// Create New Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $user_id = $_SESSION['user_id'];
    $activity_name = $_POST['activity_name'];
    $dateParts = explode('-', $_POST['date']);
    if (count($dateParts) === 3) {
        $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    } else {
        echo "Invalid date format";
    }
    $location = $_POST['location'];
    $employer_organization = $_POST['employer_organization'];
    $company_field_coordinator = $_POST['company_field_coordinator'];
    $status = $_POST['status'];

    createWorkActivity($user_id, $activity_name, $formattedDate, $location, $employer_organization, $company_field_coordinator, $status);
    $_SESSION['message'] = 'Data created successfully.';
    header("Location: dashboard.php");
    exit;
}

// Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['update_id'];
    $activity_name = $_POST['activity_name'];
    $dateParts = explode('-', $_POST['date']);
    if (count($dateParts) === 3) {
        $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    } else {
        echo "Invalid date format";
    }
    $location = $_POST['location'];
    $employer_organization = $_POST['employer_organization'];
    $company_field_coordinator = $_POST['company_field_coordinator'];
    $status = $_POST['status'];

    updateWorkActivity($id, $activity_name, $formattedDate, $location, $employer_organization, $company_field_coordinator, $status);
    $_SESSION['message'] = 'Data updated successfully.';
    header("Location: dashboard.php");
    exit;
}

// Delete Data
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    deleteWorkActivity($delete_id);
    $_SESSION['message'] = 'Data deleted successfully.';
    header("Location: dashboard.php");
    exit;
}
