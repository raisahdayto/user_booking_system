<?php
include("connect/session_check.php");

ob_start();
$styles = ob_get_clean();
ob_start();
?>
<body>
<main>
    <section class="hero-section d-flex align-items-center justify-content-center text-center text-white" style="min-height: 100vh;">
        <div class="grid grid1"></div>
        <div class="grid grid2"></div>
        <div class="grid grid3"></div>
        <div class="text-and-buttons-container">
            <h1>Welcome to Bayanlabs Inc. Booking Site</h1>
            <p>To book your appointment, please create an account. Already have an account? Click the Log-in button.</p><br>
            <div class="row mt-3 mb-3 justify-content-center">
                <div class="col-auto">
                    <form action="login.php" method="get">
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </form>
                </div>
                <div class="col-auto">
                    <form action="signup.php" method="get">
                        <button type="submit" class="btn btn-light text-primary">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
<?php $content = ob_get_clean(); ?>
<?php ob_start(); ?>
<?php $scripts = ob_get_clean(); ?>
<?php $in_concat = true; include 'layouts/base.php'; ?>
<script src="assets/js/default.js?=<?php echo $randomNumber; ?>"></script>