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
    <h2>User Login</h2>
    <form id="loginForm">
    <div class="mb-3 mt-3">
        <label>Username:</label><br>
        <input type="text" name="usernameLogin" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Password:</label><br>
        <input type="password" name="passwordLogin" class="form-control" required>
        <div style= 'color: red; font-size: smaller' id="loginMessage"></div>
        <br>
        <input type="hidden" name="action" value="login">
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</div>
</div>
</div> 
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var form = document.getElementById('loginForm');
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        fetch('server/user_operation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                // Redirect to homepage or dashboard upon successful login
                window.location.href = 'homepage.php';
            } else {
                // Display an error message if login fails
                document.getElementById('loginMessage').textContent = 'Invalid username or password. Please try again.';
                setTimeout(function() {
        window.location.reload();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('loginMessage').textContent = 'Login failed due to a technical issue. Please try again later.';
        });
    });
});
</script>
</main>

<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat= true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>
