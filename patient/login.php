<?php

$pageTitle = 'Patient Login';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT * FROM patients WHERE email=?'
    );

    $stmt->execute([$email]);

    $patient = $stmt->fetch();

    if (
        $patient &&
        password_verify($password, $patient['password'])
    ) {

        $_SESSION['patient_id'] = $patient['patient_id'];
        $_SESSION['patient_name'] = $patient['name'];

        header('Location: book.php');
        exit;
    }

    $error = 'Invalid email or password.';
}

?>

<div class="patientlogin-page">

    <!-- Decorative background -->
    <div class="patientlogin-orb patientlogin-orb-one"></div>
    <div class="patientlogin-orb patientlogin-orb-two"></div>


    <div class="patientlogin-wrapper">


        <!-- =================================================
             INTRO
             ================================================= -->

        <div class="patientlogin-intro">

            <div class="patientlogin-brand-icon">
                +
            </div>

            <span class="patientlogin-eyebrow">
                PATIENT PORTAL
            </span>

            <h1>
                Your health,<br>
                your care.
            </h1>

            <p>
                Sign in to find clinics, choose doctors and
                book your appointment easily.
            </p>


            <div class="patientlogin-features">

                <div class="patientlogin-feature">

                    <span class="patientlogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Find a Clinic</strong>
                        <small>Explore available clinics</small>
                    </div>

                </div>


                <div class="patientlogin-feature">

                    <span class="patientlogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Choose Your Doctor</strong>
                        <small>View doctors and specializations</small>
                    </div>

                </div>


                <div class="patientlogin-feature">

                    <span class="patientlogin-feature-icon">
                        ✓
                    </span>

                    <div>
                        <strong>Book an Appointment</strong>
                        <small>Schedule your visit easily</small>
                    </div>

                </div>

            </div>

        </div>


        <!-- =================================================
             LOGIN CARD
             ================================================= -->

        <div class="patientlogin-card">


            <!-- Card header -->

            <div class="patientlogin-card-top">

                <div class="patientlogin-card-icon">
                    +
                </div>

                <div>

                    <span class="patientlogin-card-label">
                        PATIENT ACCESS
                    </span>

                    <h2>
                        Patient Login
                    </h2>

                    <p>
                        Sign in to continue booking your appointment.
                    </p>

                </div>

            </div>


            <!-- Registration success -->

            <?php if (isset($_GET['registered'])): ?>

                <div class="patientlogin-success">

                    <div class="patientlogin-success-icon">
                        ✓
                    </div>

                    <div>

                        <strong>
                            Registration successful
                        </strong>

                        <p>
                            Your account has been created. Please log in.
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Login error -->

            <?php if ($error): ?>

                <div class="patientlogin-error">

                    <div class="patientlogin-error-icon">
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


            <!-- Login form -->

            <form
                method="post"
                class="patientlogin-form"
            >


                <!-- Email -->

                <div class="patientlogin-field">

                    <label for="patientlogin-email">
                        Email Address
                    </label>

                    <div class="patientlogin-input-wrap">

                        <span class="patientlogin-input-icon">
                            @
                        </span>

                        <input
                            id="patientlogin-email"
                            type="email"
                            name="email"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            required
                        >

                    </div>

                </div>


                <!-- Password -->

                <div class="patientlogin-field">

                    <label for="patientlogin-password">
                        Password
                    </label>

                    <div class="patientlogin-input-wrap">

                        <span class="patientlogin-input-icon">
                            ●
                        </span>

                        <input
                            id="patientlogin-password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="patientlogin-password-toggle"
                            onclick="patientloginTogglePassword()"
                            aria-label="Show password"
                        >
                            Show
                        </button>

                    </div>

                </div>


                <!-- Login -->

                <button
                    type="submit"
                    class="patientlogin-submit"
                >

                    <span>
                        Login to Patient Portal
                    </span>

                    <b>
                        →
                    </b>

                </button>

            </form>


            <!-- Registration -->

            <div class="patientlogin-register">

                <span>
                    Don't have an account?
                </span>

                <a href="register.php">
                    Create an account
                </a>

            </div>


            <!-- Security -->

            <div class="patientlogin-security">

                <span>
                    ✓
                </span>

                Secure patient access

            </div>

        </div>

    </div>

</div>


<script>

function patientloginTogglePassword() {

    const password = document.getElementById(
        'patientlogin-password'
    );

    const button = document.querySelector(
        '.patientlogin-password-toggle'
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