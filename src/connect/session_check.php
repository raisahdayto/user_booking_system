<?php
session_start();
// Prevent caching of the page to avoid unauthorized access after logout
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("variables.php"); // Assuming this file contains necessary configurations

$dirFileName = basename(dirname($_SERVER['PHP_SELF']));
$currentPage = basename($_SERVER['PHP_SELF']);

// Define the pages that require the user to be logged in to access them
$protectedPages = ['homepage.php', 'bookingform.php', 'bookingsdisplay.php', 'payment.php'];

// Define admin protected pages
$adminProtectedPages = ['bookingManagement.php', 'dashboardView.php', 'feedbackView.php'];

// Define pages that should only be accessible when the user is not logged in
$publicOnlyPages = ['login.php', 'signup.php', 'index.php'];

// Admin login page that is accessible without being logged in
$adminPublicOnlyPages = ['index.php'];

// Reset the bookingComplete flag if the reset parameter is provided
if (isset($_POST['reset']) && $_POST['reset'] === 'true') {
    $_SESSION['bookingComplete'] = false; // Reset the flag
    header('Content-Type: application/json');
    echo json_encode(['status' => true]); // Send back a success status
    exit; // Important to prevent further script execution
}

// Check for AJAX request to reset session variable
if (isset($_POST['reset']) && $_POST['reset'] === 'true') {
    // Reset the bookingComplete session variable
    $_SESSION['bookingComplete'] = false;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['status' => true]);
    exit; // Stop script execution after handling AJAX request
}

// Additional logic to prevent back navigation to booking and payment forms after completion
if (isset($_SESSION['bookingComplete']) && ($_SESSION['bookingComplete'] == true) && 
    (in_array($currentPage, ['bookingform.php', 'payment.php']))) {
    header("Location: homepage.php");
    exit;
}

// Redirect logic for pages protected by user login
if (in_array($currentPage, $protectedPages) && !isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// Redirect logic for admin protected pages
if (in_array($currentPage, $adminProtectedPages) && !isset($_SESSION['adminid'])) {
    header("Location: index.php");
    exit;
}

// Redirect logic for user pages that should only be accessible when not logged in
if (in_array($currentPage, $publicOnlyPages) && isset($_SESSION['userid'])) {
    header("Location: homepage.php");
    exit;
}

// Adjusted redirection logic for the admin login page
if (in_array($currentPage, $adminPublicOnlyPages)) {
    if (isset($_SESSION['adminid']) && !isset($_SESSION['has_logged_out'])) {
        header("Location: dashboardView.php");
        exit;
    }
    unset($_SESSION['has_logged_out']); // Clear flag after checking it
}

// IP Address detection logic
if (in_array($_SERVER['REMOTE_ADDR'], $localhostTrue)) {
    // Assuming localhost
    $ip = "49.150.164.88"; // Example IP, adjust as necessary
} else {
    // Assuming live server
    $ip = getenv('REMOTE_ADDR');
}

// Check if running on localhost
$localhost_status = in_array($_SERVER['REMOTE_ADDR'], $localhostTrue);
$localhost_status_index = $localhost_status && ($dirFileName == "dist");

// Determine if the script is running in an index or registration context
$index_status = !$localhost_status || $currentPage == "register.php" || $dirFileName == "dist" || $dirFileName == "";

// Execute options based on environment and page context
$in_concat = ($localhost_status && $localhost_status_index) || (!$localhost_status && $index_status);

?>
