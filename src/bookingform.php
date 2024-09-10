<?php
include("connect/session_check.php");
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
                    <a class="nav-link" href="homepage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bookingsdisplay.php">View My Bookings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="bookingform.php" id="bookingFormButton1">Book Now</a>
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
<div class="container p-5">
<div class="row justify-content-center">
<div class="col-md-8 col-lg-6">
<div class="card-body bg-light p-5 rounded">
    <h2>Booking Form</h2>
    <form id="bookingForm">
    <div class="mb-3 mt-3">
        <label>First Name:</label><br>
        <input type="text" name="firstname" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Last Name:</label><br>
        <input type="text" name="lastname" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Email:</label><br>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Phone Number:</label><br>
        <input type="number" name="number" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="regDateInput">Registration Date:</label><br>
        <input type="text" id="regDateInput" name="reg_date" class="form-control" required>
        <div style='color: red; font-size: smaller' id="bookingMessage"></div>
    </div>
        <input type="hidden" name="action" value="booking">
        <button type="submit" class="btn btn-primary">Proceed</button>
    </form>
</div>
</div>
</div>
</div> 
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

        // Fetch Disabled Dates for the Registration Date Input
        fetch('server/user_operation.php', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=fetchDisabledDates'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                var disabledDates = data.disabledDates;
                flatpickr("input[name='reg_date']", {
                    dateFormat: "Y-m-d",
                    disable: disabledDates.map(date => new Date(date)),
                    minDate: "today",
                });
            } else {
                console.error('Failed to fetch disabled dates:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching disabled dates:', error);
        });

        // Booking Form Submission Event Listener
        const bookingForm = document.getElementById('bookingForm');
        if(bookingForm) {
            bookingForm.addEventListener('submit', function(event) {
                event.preventDefault();
                var regDateInput = document.getElementById('regDateInput');
                if (!regDateInput.value) {
                    document.getElementById('bookingMessage').textContent = 'Please fill out all the necessary fields.';
                    return;
                }
                var formData = new FormData(this);
                formData.forEach((value, key) => sessionStorage.setItem(key, value));
                window.location.href = 'payment.php'; // Redirect if form is valid
            });
        }
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
