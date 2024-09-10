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
                        <a class="nav-link" href="homepage.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="bookingsdisplay.php">View My Bookings</a>
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
    <!-- Secondary navbar for booking status -->
    <nav class="navbar navbar-expand-lg navbar-light mt-2" id="secondaryNavbar">
        <div class="container">
            <div class="navbar-nav mx-auto">
                <button class="nav-link active small" style="background:none; border:none; cursor:pointer; padding:3;" onclick="openTab(event, 'Pending')">Pending</button>
                <button class="nav-link small" style="background:none; border:none; cursor:pointer; padding:3;" onclick="openTab(event, 'Approved')">Approved</button>
                <button class="nav-link small" style="background:none; border:none; cursor:pointer; padding:3;" onclick="openTab(event, 'Completed')">Completed</button>
                <button class="nav-link small" style="background:none; border:none; cursor:pointer; padding:3;" onclick="openTab(event, 'Cancelled')">Cancelled</button>
            </div>
        </div>
    </nav>
    <!-- Container for the booking tabs -->
    <div class="container mt-4">
        <div id="Pending" class="tabcontent" style="display:none;">
            <div id="pendingBookingsContainer"></div>
        </div>
        <div id="Approved" class="tabcontent" style="display:none;">
            <div id="approvedBookingsContainer"></div>
        </div>
        <div id="Completed" class="tabcontent" style="display:none;">
            <div id="completedBookingsContainer"></div>
        </div>
        <div id="Cancelled" class="tabcontent" style="display:none;">
            <div id="cancelledBookingsContainer"></div>
        </div>
    </div>
</main>

<script>

document.addEventListener("DOMContentLoaded", function() {
    // Activate the "View My Bookings" tab directly if needed
    openTab(null, 'Pending'); // Null can be used if event is not needed in openTab
    
    // Fetch bookings right away
    fetchBookingsByStatus();
    
    // Logout Link Event Listener
    const logoutLink = document.getElementById('logout');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default link action
            logout();
        });
    }

    document.querySelectorAll('#bookingFormButton1').forEach(button => {
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

function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    // Target only links within the secondary navbar using its unique ID
    tablinks = document.querySelectorAll('#secondaryNavbar .nav-link');
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].classList.remove("active", "border-bottom", "border-2", "border-primary");
    }
    if (document.getElementById(tabName)) {
        document.getElementById(tabName).style.display = "block";
    }
    // Find the active tab link specifically within the secondary navbar
    var activeTabLink = document.querySelector('#secondaryNavbar .nav-link[onclick="openTab(event, \'' + tabName + '\')"]');
    if (activeTabLink) {
        activeTabLink.classList.add("active", "border-bottom", "border-2", "border-primary");
    }
}

function fetchBookingsByStatus() {
    fetch('server/user_operation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=bookinglist'
    })
    .then(response => response.json())
    .then(data => {
        const statuses = ['Pending', 'Approved', 'Cancelled', 'Completed']; // Include 'Completed'
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const todayStr = today.toISOString().split('T')[0];
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        
        statuses.forEach((status) => {
            const container = document.getElementById(`${status.toLowerCase()}BookingsContainer`);
            if (data.status && data.bookings[status] && data.bookings[status].length > 0) {
                container.innerHTML = '';
                const rowDiv = document.createElement('div');
                rowDiv.className = 'row text-center';
                
                data.bookings[status].forEach(booking => {
                    const regDate = new Date(booking.reg_date);
                    const regDateStr = regDate.toISOString().split('T')[0];
                    let displayDate = regDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    if (regDateStr === todayStr) {
                        displayDate = 'Today';
                    } else if (regDateStr === tomorrowStr) {
                        displayDate = 'Tomorrow';
                    }
                    
                    const cardDiv = document.createElement('div');
                    cardDiv.classList.add('col-12', 'mb-4');
                    cardDiv.innerHTML = `
                        <div class="card h-100 border-0 shadow">
                            <div class="card-header bg-primary text-white align-middle"><h4 class="align-middle">${displayDate}</h4></div>
                            <div class="card-body bg-light">
                                <h5 class="card-title align-middle">${booking.firstname} ${booking.lastname}</h5>
                                <p class="card-text align-middle">Email: ${booking.email}</p>
                                <p class="card-text align-middle">Phone: ${booking.number}</p>
                                <p class="card-text align-middle">Package: ${booking.package_name}</p>
                                <p class="card-text align-middle">Amount: ${booking.amount}</p>
                                <p class="card-text align-middle">Payment Date: ${new Date(booking.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                <p class="card-text align-middle">Payment Status: ${booking.payment_status}</p>
                                ${status === 'Pending' ? `<button class="btn btn-danger" onclick="cancelBooking(${booking.bookingid}, this)">Cancel this booking</button>` : ''}
                            </div>
                        </div>
                    `;
                    rowDiv.appendChild(cardDiv);
                });

                container.appendChild(rowDiv);
            } else {
                container.innerHTML = `<p>No ${status.toLowerCase()} bookings found.</p>`;
            }
        });
    })
    .catch(error => {
        console.error('Error fetching bookings:', error);
        alert('Failed to fetch bookings. Please try again.');
    });
}

function cancelBooking(bookingid, element) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }

    let formData = new FormData();
    formData.append('action', 'cancelBooking');
    formData.append('bookingid', bookingid);

    fetch('server/user_operation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            alert('Booking cancelled successfully.');
            element.closest('.card').remove(); // Remove from current view
            fetchBookingsByStatus(); // Refresh to reflect the change accurately
        } else {
            alert(data.message); // Show why the booking couldn't be cancelled
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat = true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>
