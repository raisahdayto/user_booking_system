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
<div class="card-body bg-light p-5 rounded">
    <h2>Sign Up</h2>
    <form id="signupForm">
    <div class="mb-3 mt-3">
        <label for="usernameInput">Username:</label><br>
        <input type="text" id="usernameInput" name="usernameSignup" class="form-control" required>
        <div style='color: red; font-size: smaller' id="signupMessage"></div>
    </div>
    <div class="mb-3">
        <label for="passwordInput">Password:</label><br>
        <input type="password" id="passwordInput" name="passwordSignup" class="form-control" required>
        <input type="hidden" name="action" value="signup">
    </div>
        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>
</div>
</div>
</div>
</div> 
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById('signupForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        formData.append('action', 'signup'); // Ensure the action parameter is included.
        fetch('server/user_operation.php', { // Adjust path as necessary.
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Redirect to homepage or login page upon successful signup
                window.location.href = 'homepage.php';
            } else {
                // Display an error message if signup fails
                document.getElementById('signupMessage').textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('signupMessage').textContent = 'Signup failed due to a technical issue. Please try again later.';
        });
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat= true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>
