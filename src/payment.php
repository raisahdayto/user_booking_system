<?php
include("connect/session_check.php");

ob_start();
$styles = ob_get_clean();
ob_start();
?>

<main>
<div class="container p-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card-body bg-light p-5">
                <h2>Choose Your Package</h2>
                <form id="paymentForm">
                    <div class="mb-3 mt-3" id="packagesContainer">
                        <!-- Packages will be loaded here by JavaScript -->
                    </div>
                    <input type="hidden" id="hiddenBookingId" name="bookingid" value="">
                    <div id="paymentMessage" style='color: red; font-size: smaller'></div>
                    <button type="button" class="btn btn-primary" onclick="submitPayment()">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Fetch packages
    fetchPackages();

    // Retrieve and set the bookingid from the URL query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const bookingid = urlParams.get('bookingid');
    document.getElementById('hiddenBookingId').value = bookingid;
});

function fetchPackages() {
    fetch('server/user_operation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=fetchPackages'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('packagesContainer');
        if (data.status && data.packages.length > 0) {
            data.packages.forEach(pkg => {
                const div = document.createElement('div');
                div.innerHTML = `<input type="radio" name="packageid" value="${pkg.packageid}"> ${pkg.package_name} - $${pkg.package_price}`;
                container.appendChild(div);
            });
        } else {
            container.innerHTML = `<p>No packages found.</p>`;
        }
    })
    .catch(error => {
        console.error('Error fetching packages:', error);
        document.getElementById('packagesContainer').innerHTML = 'Failed to load packages. Please try again.';
    });
}

function submitPayment() {
    var formData = new FormData(document.getElementById('paymentForm'));
    formData.append('action', 'processBookingAndPayment');
    formData.append('packageid', document.querySelector('input[name="packageid"]:checked').value);

    ['firstname', 'lastname', 'email', 'number', 'reg_date'].forEach(key => {
        formData.append(key, sessionStorage.getItem(key));
    });

    fetch('server/user_operation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status) {
            alert("Payment and Booking successful");
            sessionStorage.clear(); // Clear session storage on successful booking and payment
            window.location.href = 'homepage.php'; // Redirect to the homepage
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat= true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>