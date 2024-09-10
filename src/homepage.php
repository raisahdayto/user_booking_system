<?php
include("connect/session_check.php");

ob_start();
$styles = ob_get_clean();
ob_start();
?>

<main>
<nav class="navbar navbar-expand-lg navbar-light bg-light" id="primaryNavbar">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookingsdisplay.php">View My Bookings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bookingform.php" id="bookingFormButton1">Book Now</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                    <a class="nav-link" href="#" id="logout">Log Out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
        <section class="hero-section d-flex align-items-center justify-content-center text-center text-white" style="min-height: 100vh;">
            <div>
                <h2>Revolutionizing Your Booking Experience</h2>
                <p>Bayanlabs Inc. introduces an innovative booking system designed to streamline your scheduling needs.</p><br>
                <a href="bookingform.php" class="btn btn-primary" id="bookingFormButton2">Book Now</a>
            </div>
        </section>
    </main>
</main>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Logout Link Event Listener
    const logoutLink = document.getElementById('logout');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default link action
            logout();
        });
    }

    // Handle clicks on "Book Now" buttons
    document.querySelectorAll('#bookingFormButton1, #bookingFormButton2').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default anchor behavior
            const href = this.getAttribute('href'); // Get the href attribute

            // Perform a POST request to reset the session variable
            fetch('connect/session_check.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'reset=true'
            })
            .then(response => response.json())
            .then(data => {
                if(data.status) {
                    window.location.href = href; // Redirect to the booking form
                } else {
                    console.error('Session reset failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});

function logout() {
    fetch('server/user_operation.php', { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=logout'
    })
    .then(response => response.json())
    .then(data => {
        if(data.status) {
            window.location.href = 'index.php';
        } else {
            console.error('Logout failed:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat = true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>
