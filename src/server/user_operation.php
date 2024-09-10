<?php
include_once("../connect/config.php");
include_once("../connect/session_check.php");

function jsonResponse($status, $message, $additionalData = []) {
    header('Content-Type: application/json'); // Ensure JSON content type
    echo json_encode(array_merge([
        'status' => $status,
        'message' => $message
    ], $additionalData));
    exit;
}

function handleSignup($conn) {
    if (!isset($_POST['usernameSignup'], $_POST['passwordSignup'])) {
        jsonResponse(false, "Username and password are required.");
        return;
    }

    $username = $_POST['usernameSignup'];
    $password = $_POST['passwordSignup'];

    $checkSql = "SELECT userid FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        jsonResponse(false, "Username already taken, please choose another.");
        return;

    }
    
    $checkStmt->close();

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $passwordHash);

    if ($stmt->execute()) {
        $_SESSION['userid'] = $conn->insert_id;
        session_regenerate_id();
        jsonResponse(true, "Signup successful.", ['redirectUrl' => '../homepage.php']);
    } else {
        jsonResponse(false, "An error occurred during sign-up. Please try again.");
    }
    $stmt->close();
}

function handleLogin($conn) {
    if (!isset($_POST['usernameLogin'], $_POST['passwordLogin'])) {
        jsonResponse(false, "Username and password are required.");
        return;
    }

    $username = $_POST['usernameLogin'];
    $password = $_POST['passwordLogin'];

    $stmt = $conn->prepare("SELECT userid, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['userid'] = $user['userid'];
            session_regenerate_id();
            jsonResponse(true, "Login successful.", ['redirectUrl' => '../homepage.php']);
        } else {
            jsonResponse(false, "Invalid username or password.");
        }
    } else {
        jsonResponse(false, "Invalid username or password.");
    }
    $stmt->close();
}

function handleLogout() {
    // Destroy the session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 1,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();
    
    jsonResponse(true, "Logout successful.");
}

function fetchPackages($conn) {
    $query = "SELECT packageid, package_name, package_price FROM packages";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
        jsonResponse(true, "Packages fetched successfully.", ['packages' => $packages]);
    } else {
        jsonResponse(false, "No packages found.");
    }
}

function processBookingAndPayment($conn) {
    // Check for the presence of all required fields
    if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['number'], $_POST['reg_date'], $_POST['packageid'])) {
        jsonResponse(false, "All necessary fields are required.");
        return;
    }
    
    // Check for empty values in required fields
    foreach (['firstname', 'lastname', 'email', 'number', 'reg_date', 'packageid'] as $field) {
        if (empty(trim($_POST[$field]))) {
            jsonResponse(false, "All fields must be filled out.");
            return;
        }
    }

    // Sanitization of inputs
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $reg_date = filter_var($_POST['reg_date'], FILTER_SANITIZE_STRING);
    $packageid = filter_var($_POST['packageid'], FILTER_VALIDATE_INT);

    if (false === $packageid) {
        jsonResponse(false, "Invalid package ID provided.");
        return;
    }

    $userid = $_SESSION['userid']; // Assuming $_SESSION['userid'] is already set

    // Start transaction
    $conn->begin_transaction();
    try {
        // Fetch package price
        $packageStmt = $conn->prepare("SELECT package_price FROM packages WHERE packageid = ?");
        $packageStmt->bind_param("i", $packageid);
        $packageStmt->execute();
        $packageResult = $packageStmt->get_result();
        if ($packageResult->num_rows === 0) {
            throw new Exception("Invalid package ID.");
        }
        $package = $packageResult->fetch_assoc();
        $packagePrice = $package['package_price'];

        // Insert booking with initial status
        $initialStatus = 'Pending'; // Default status
        $bookingStmt = $conn->prepare("INSERT INTO bookings (userid, firstname, lastname, email, number, reg_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $bookingStmt->bind_param("issssss", $userid, $firstname, $lastname, $email, $number, $reg_date, $initialStatus);
        $bookingStmt->execute();
        $bookingid = $conn->insert_id;

        // Insert payment details assuming payment is successful
        $paymentStatus = 'Completed'; // Assuming payment is immediately completed for this context
        $paymentStmt = $conn->prepare("INSERT INTO payments (userid, bookingid, packageid, amount, payment_date, payment_status) VALUES (?, ?, ?, ?, NOW(), ?)");
        $paymentStmt->bind_param("iiiss", $userid, $bookingid, $packageid, $packagePrice, $paymentStatus); // Corrected
        $paymentStmt->execute();

        // If payment is successful, update booking status to 'Approved'
        if ($paymentStatus == 'Completed') {
            $updateBookingStmt = $conn->prepare("UPDATE bookings SET Status = 'Approved' WHERE bookingid = ?");
            $updateBookingStmt->bind_param("i", $bookingid);
            $updateBookingStmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        $_SESSION['bookingComplete'] = true; // Indicate that booking and payment have been completed
        jsonResponse(true, "Booking and payment processed successfully.", ['bookingid' => $bookingid]);
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        jsonResponse(false, "An error occurred: " . $e->getMessage());
    }
}

function fetchDisabledDates($conn) {
    $disabledDates = [];
    $query = "SELECT DISTINCT reg_date FROM bookings";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $disabledDates[] = $row['reg_date'];
        }
    }
    
    jsonResponse(true, "Disabled dates fetched successfully.", ['disabledDates' => $disabledDates]);
}

function displayUserBookings($conn, $userid) {
    $today = date('Y-m-d'); // Current date to compare against booking dates
    $statuses = ['Pending', 'Approved', 'Cancelled'];
    $bookings = ['Pending' => [], 'Approved' => [], 'Cancelled' => [], 'Completed' => []];

    foreach ($statuses as $status) {
        $stmt = $conn->prepare("
            SELECT 
                b.bookingid, b.firstname, b.lastname, b.email, b.number, b.reg_date, b.Status,
                p.amount, p.payment_date, p.payment_status,
                pk.package_name
            FROM bookings AS b
            LEFT JOIN payments AS p ON b.bookingid = p.bookingid
            LEFT JOIN packages AS pk ON p.packageid = pk.packageid
            WHERE b.userid = ? AND b.Status = ?
            ORDER BY b.reg_date ASC
        ");
        $stmt->bind_param("is", $userid, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($status == 'Approved' && $row['reg_date'] < $today) {
                // Move past 'Approved' bookings to 'Completed'
                $bookings['Completed'][] = $row;
            } else {
                $bookings[$status][] = $row;
            }
        }
        $stmt->close();
    }

    if (array_sum(array_map("count", $bookings)) > 0) {
        jsonResponse(true, "Bookings found.", ['bookings' => $bookings]);
    } else {
        jsonResponse(false, "No bookings found.");
    }
}


function handleCancellation($conn) {
    $bookingid = $_POST['bookingid'];
    $userid = $_SESSION['userid'];

    error_log("Attempting to cancel booking with ID: $bookingid for user ID: $userid");

    $stmt = $conn->prepare("SELECT Status FROM bookings WHERE bookingid = ? AND userid = ?");
    $stmt->bind_param("ii", $bookingid, $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();

        if ($booking['Status'] === 'Pending') {
            $updateStmt = $conn->prepare("UPDATE bookings SET Status = 'Cancelled' WHERE bookingid = ?");
            $updateStmt->bind_param("i", $bookingid);
            if ($updateStmt->execute()) {
                error_log("Booking with ID: $bookingid successfully cancelled.");
                jsonResponse(true, "Booking cancelled successfully.");
            } else {
                error_log("Error occurred while updating booking status for ID: $bookingid");
                jsonResponse(false, "An error occurred while updating the booking status.");
            }
        } elseif ($booking['Status'] === 'Cancelled') {
            jsonResponse(false, "This booking has already been cancelled.");
        } else {
            jsonResponse(false, "Booking cannot be cancelled once approved or does not belong to you.");
        }
    } else {
        error_log("No booking found with ID: $bookingid for user ID: $userid or it does not belong to the user");
        jsonResponse(false, "Booking not found or does not belong to you.");
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'signup':
                handleSignup($conn);
                break;
            case 'login':
                handleLogin($conn);
                break;
            case 'logout':
                handleLogout();
                break;
            case 'fetchPackages':
                fetchPackages($conn);
                break;
            case 'fetchDisabledDates':
                fetchDisabledDates($conn);
                break;
            case 'processBookingAndPayment':
                processBookingAndPayment($conn);
                break;
            case 'bookinglist':
                if (isset($_SESSION['userid'])) {
                    $userid = $_SESSION['userid'];
                    displayUserBookings($conn, $userid);
                } else {
                    jsonResponse(false, "User ID not found. Please log in.");
                }
                break;
            case 'cancelBooking':
                if (isset($_POST['bookingid'])) {
                    handleCancellation($conn);
                } else {
                    jsonResponse(false, "Booking ID is required.");
                }
                break;
            default:
                jsonResponse(false, "Invalid action.");
                break;
        }
    }
}
?>