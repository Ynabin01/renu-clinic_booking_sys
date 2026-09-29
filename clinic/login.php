<?php

$pageTitle = 'Clinic Login';
$basePath = '../';

require '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    if ($u === 'admin' && $p === 'admin123') {

        $_SESSION['clinic_admin'] = true;

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Demo login details are incorrect.';
}

?>

<div class="cliniclogin-page">

    <!-- Decorative background -->
    <div class="cliniclogin-orb cliniclogin-orb-one"></div>
    <div class="cliniclogin-orb cliniclogin-orb-two"></div>


    <div class="cliniclogin-wrapper">

        <!-- LEFT / BRAND AREA -->
        <div class="cliniclogin-intro">

            <div class="cliniclogin-brand-icon">
                +
            </div>

            <span class="cliniclogin-eyebrow">
                CLINIC MANAGEMENT
            </span>

            <h1>
                Welcome back.
            </h1>

            <p>
                Manage clinics, doctors and appointments from one
                simple administration panel.
            </p>


            <div class="cliniclogin-features">

                <div class="cliniclogin-feature">

                    <span class="cliniclogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Clinic Management</strong>
                        <small>Manage clinic information easily</small>
                    </div>

                </div>


                <div class="cliniclogin-feature">

                    <span class="cliniclogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Doctor Management</strong>
                        <small>Add and manage clinic doctors</small>
                    </div>

                </div>


                <div class="cliniclogin-feature">

                    <span class="cliniclogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Booking Control</strong>
                        <small>Keep your clinic operations organized</small>
                    </div>

                </div>

            </div>

        </div>


        <!-- LOGIN CARD -->
        <div class="cliniclogin-card">

            <div class="cliniclogin-card-top">

                <div class="cliniclogin-card-icon">
                    +
                </div>

                <div>

                    <span class="cliniclogin-card-label">
                        ADMIN ACCESS
                    </span>

                    <h2>
                        Staff Login
                    </h2>

                    <p>
                        Sign in to continue to your dashboard.
                    </p>

                </div>

            </div>


            <?php if (!empty($error)): ?>

                <div class="cliniclogin-error">

                    <div class="cliniclogin-error-icon">
                        !
                    </div>

                    <div>

                        <strong>
                            Login failed
                        </strong>

                        <p>
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <form
                method="post"
                class="cliniclogin-form"
            >

                <div class="cliniclogin-field">

                    <label for="cliniclogin-username">
                        Username
                    </label>

                    <div class="cliniclogin-input-wrap">

                        <span class="cliniclogin-input-icon">
                            ◉
                        </span>

                        <input
                            id="cliniclogin-username"
                            type="text"
                            name="username"
                            placeholder="Enter your username"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <div class="cliniclogin-field">

                    <label for="cliniclogin-password">
                        Password
                    </label>

                    <div class="cliniclogin-input-wrap">

                        <span class="cliniclogin-input-icon">
                            ●
                        </span>

                        <input
                            id="cliniclogin-password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="cliniclogin-password-toggle"
                            onclick="clinicloginTogglePassword()"
                            aria-label="Show password"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="cliniclogin-submit"
                >

                    <span>
                        Login to Dashboard
                    </span>

                    <b>
                        →
                    </b>

                </button>

            </form>


            <!-- DEMO ACCOUNT -->
            <!-- <div class="cliniclogin-demo">

                <div class="cliniclogin-demo-title">
                    Demo Account
                </div>

                <div class="cliniclogin-demo-row">

                    <span>
                        Username
                    </span>

                    <strong>
                        admin
                    </strong>

                </div>

                <div class="cliniclogin-demo-row">

                    <span>
                        Password
                    </span>

                    <strong>
                        admin123
                    </strong>

                </div>

            </div> -->


            <div class="cliniclogin-security">

                <span>✓</span>

                Secure administrator access

            </div>

        </div>

    </div>

</div>


<script>
function clinicloginTogglePassword() {

    const password = document.getElementById(
        'cliniclogin-password'
    );

    const button = document.querySelector(
        '.cliniclogin-password-toggle'
    );

    if (password.type === 'password') {

        password.type = 'text';
        button.textContent = 'Hide';

    } else {

        password.type = 'password';
        button.textContent = 'Show';
    }
}
</script>


<?php require '../includes/footer.php'; ?>