<?php
$pageTitle = 'Patient Registration';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        strlen($password) < 6
    ) {
        $error = 'Please complete all fields. Password must be at least 6 characters.';
    } else {

        try {

            $stmt = $pdo->prepare(
                'INSERT INTO patients(name,email,password,phone)
                 VALUES(?,?,?,?)'
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $phone
            ]);

            header('Location: login.php?registered=1');
            exit;

        } catch (PDOException $e) {

            $error = 'This email may already be registered.';
        }
    }
}
?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/patient-register.css">

<section class="patientregister-page">

    <div class="patientregister-container">

        <div class="patientregister-layout">

            <!-- Left Information Panel -->
            <div class="patientregister-intro">

                <div class="patientregister-intro-content">

                    <div class="patientregister-eyebrow">
                        PATIENT PORTAL
                    </div>

                    <h1>
                        Create your
                        <span>patient account.</span>
                    </h1>

                    <p class="patientregister-intro-text">
                        Register once and easily manage your clinic appointments,
                        doctors and booking information from your patient portal.
                    </p>


                    <div class="patientregister-features">

                        <div class="patientregister-feature">

                            <div class="patientregister-feature-number">
                                01
                            </div>

                            <div>
                                <strong>Find a Clinic</strong>

                                <span>
                                    Choose from available clinics and services.
                                </span>
                            </div>

                        </div>


                        <div class="patientregister-feature">

                            <div class="patientregister-feature-number">
                                02
                            </div>

                            <div>
                                <strong>Choose Your Doctor</strong>

                                <span>
                                    Select an available doctor based on your needs.
                                </span>
                            </div>

                        </div>


                        <div class="patientregister-feature">

                            <div class="patientregister-feature-number">
                                03
                            </div>

                            <div>
                                <strong>Book an Appointment</strong>

                                <span>
                                    Select a suitable date and available time.
                                </span>
                            </div>

                        </div>

                    </div>


                    <div class="patientregister-intro-note">
                        Your account allows you to view and manage your
                        appointment information in one place.
                    </div>

                </div>

            </div>


            <!-- Registration Card -->
            <div class="patientregister-card">

                <div class="patientregister-card-header">

                    <div class="patientregister-card-eyebrow">
                        GET STARTED
                    </div>

                    <h2>Create Patient Account</h2>

                    <p>
                        Enter your details below to create your account.
                    </p>

                </div>


                <?php if ($error): ?>

                    <div class="patientregister-alert">

                        <span class="patientregister-alert-icon">!</span>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <form method="post" class="patientregister-form">

                    <!-- Name -->
                    <div class="patientregister-field">

                        <label for="patientName">
                            Full Name
                            <span>*</span>
                        </label>

                        <div class="patientregister-input-wrap">

                            <input
                                type="text"
                                id="patientName"
                                name="name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                placeholder="Enter your full name"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>


                    <!-- Email -->
                    <div class="patientregister-field">

                        <label for="patientEmail">
                            Email Address
                            <span>*</span>
                        </label>

                        <div class="patientregister-input-wrap">

                            <input
                                type="email"
                                id="patientEmail"
                                name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                placeholder="Enter your email address"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>


                    <!-- Phone -->
                    <div class="patientregister-field">

                        <label for="patientPhone">
                            Phone Number
                            <span>*</span>
                        </label>

                        <div class="patientregister-input-wrap">

                            <input
                                type="tel"
                                id="patientPhone"
                                name="phone"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                placeholder="Enter your phone number"
                                autocomplete="tel"
                                required
                            >

                        </div>

                    </div>


                    <!-- Password -->
                    <div class="patientregister-field">

                        <label for="patientPassword">
                            Password
                            <span>*</span>
                        </label>

                        <div class="patientregister-password-wrap">

                            <input
                                type="password"
                                id="patientPassword"
                                name="password"
                                minlength="6"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="patientregister-password-toggle"
                                id="patientPasswordToggle"
                                aria-label="Show password"
                            >
                                Show
                            </button>

                        </div>

                        <div class="patientregister-password-help">
                            Password must contain at least 6 characters.
                        </div>

                    </div>


                    <!-- Submit -->
                    <button
                        type="submit"
                        class="patientregister-submit"
                    >
                        Create Account
                    </button>

                </form>


                <!-- Login -->
                <div class="patientregister-login">

                    <span>
                        Already have an account?
                    </span>

                    <a href="login.php">
                        Sign in
                    </a>

                </div>


                <div class="patientregister-security">

                    <span class="patientregister-security-dot"></span>

                    Your password is securely encrypted.

                </div>

            </div>

        </div>

    </div>

</section>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const passwordInput = document.getElementById('patientPassword');
    const passwordToggle = document.getElementById('patientPasswordToggle');

    if (!passwordInput || !passwordToggle) {
        return;
    }

    passwordToggle.addEventListener('click', function () {

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';
            passwordToggle.textContent = 'Hide';
            passwordToggle.setAttribute(
                'aria-label',
                'Hide password'
            );

        } else {

            passwordInput.type = 'password';
            passwordToggle.textContent = 'Show';
            passwordToggle.setAttribute(
                'aria-label',
                'Show password'
            );
        }

    });

});
</script>

<?php require '../includes/footer.php'; ?>