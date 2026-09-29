<?php

$pageTitle = 'Add Clinic';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

if (empty($_SESSION['clinic_admin'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['clinic_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $address === '') {

        $error = 'Clinic name and address are required.';

    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        $stmt = $pdo->prepare(
            'INSERT INTO clinics (clinic_name, address, phone, email)
             VALUES (?,?,?,?)'
        );

        $stmt->execute([
            $name,
            $address,
            $phone,
            $email
        ]);

        header(
            'Location: manage.php?clinic_id=' .
            $pdo->lastInsertId() .
            '&added=1'
        );

        exit;
    }
}

?>

<!-- =====================================================
     ADD CLINIC PAGE
     All classes below are unique to this page.
     Prefix: clinicadd-
     ===================================================== -->

<div class="clinicadd-page">

    <div class="clinicadd-container">

        <!-- Header -->
        <div class="clinicadd-header">

            <div class="clinicadd-title-area">

                <div class="clinicadd-title-icon">
                    +
                </div>
                <div class="clinicadd-title-content">

                    <div class="clinicadd-label">
                        CLINIC MANAGEMENT
                    </div>
                    <h1>
                        Add New Clinic
                    </h1>
                    <p>
                        Create a new clinic and add its contact information.
                    </p>
                </div>

            </div>

            <a href="manage.php" class="clinicadd-back">
                <span class="clinicadd-back-arrow">←</span>
                Back to Management
            </a>
        </div>

        <!-- Main Card -->
        <div class="clinicadd-card">

            <!-- Card Header -->
            <div class="clinicadd-card-header">

                <div>

                    <h2>
                        Clinic Information
                    </h2>

                    <p>
                        Enter the basic information for the new clinic.
                    </p>

                </div>

                <div class="clinicadd-required">
                    <span>*</span> Required
                </div>

            </div>


            <!-- Error Message -->
            <?php if ($error): ?>

                <div class="clinicadd-error">

                    <div class="clinicadd-error-icon">
                        !
                    </div>

                    <div class="clinicadd-error-text">

                        <strong>
                            Please check the information
                        </strong>

                        <p>
                            <?= htmlspecialchars($error) ?>
                        </p>

                    </div>

                </div>

            <?php endif; ?>


            <!-- Form -->
            <form method="post" class="clinicadd-form">

                <!-- Clinic Name -->
                <div class="clinicadd-field">

                    <label for="clinicadd-name" class="clinicadd-field-label">
                        Clinic Name
                        <span>*</span>
                    </label>

                    <div class="clinicadd-input-box">

                        <div class="clinicadd-input-symbol">
                            +
                        </div>

                        <input id="clinicadd-name" type="text" name="clinic_name"
                            placeholder="e.g. Sunrise Medical Clinic"
                            value="<?= htmlspecialchars($_POST['clinic_name'] ?? '') ?>" required>

                    </div>

                    <div class="clinicadd-help">
                        Enter the official name of the clinic.
                    </div>

                </div>


                <!-- Address -->
                <div class="clinicadd-field">

                    <label for="clinicadd-address" class="clinicadd-field-label">
                        Clinic Address
                        <span>*</span>
                    </label>

                    <div class="clinicadd-input-box">

                        <div class="clinicadd-input-symbol">
                            ⌖
                        </div>

                        <input id="clinicadd-address" type="text" name="address"
                            placeholder="e.g. Sydney NSW, Australia"
                            value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>

                    </div>

                    <div class="clinicadd-help">
                        Enter the complete location of the clinic.
                    </div>

                </div>


                <!-- Section Divider -->
                <div class="clinicadd-divider">

                    <span>
                        CONTACT DETAILS
                    </span>

                </div>


                <!-- Phone + Email -->
                <div class="clinicadd-contact-grid">

                    <!-- Phone -->
                    <div class="clinicadd-field">

                        <label for="clinicadd-phone" class="clinicadd-field-label">
                            Phone Number
                        </label>

                        <div class="clinicadd-input-box">

                            <div class="clinicadd-input-symbol">
                                ☎
                            </div>

                            <input id="clinicadd-phone" type="text" name="phone" placeholder="01-XXXXXXX"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">

                        </div>

                        <div class="clinicadd-help">
                            Optional contact number.
                        </div>

                    </div>


                    <!-- Email -->
                    <div class="clinicadd-field">

                        <label for="clinicadd-email" class="clinicadd-field-label">
                            Email Address
                        </label>

                        <div class="clinicadd-input-box">

                            <div class="clinicadd-input-symbol">
                                @
                            </div>

                            <input id="clinicadd-email" type="email" name="email" placeholder="clinic@example.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                        </div>

                        <div class="clinicadd-help">
                            Optional clinic email address.
                        </div>

                    </div>

                </div>


                <!-- Buttons -->
                <div class="clinicadd-actions">

                    <a href="manage.php" class="clinicadd-cancel">
                        Cancel
                    </a>

                    <button type="submit" class="clinicadd-submit">
                        <span>✓</span>
                        Add Clinic
                    </button>

                </div>

            </form>

        </div>


        <!-- Information -->
        <div class="clinicadd-info">

            <div class="clinicadd-info-icon">
                i
            </div>

            <div class="clinicadd-info-content">

                <strong>
                    What's next?
                </strong>

                <span>
                    After adding the clinic, you can add doctors and manage their availability from the management page.
                </span>

            </div>

        </div>

    </div>

</div>


<?php require '../includes/footer.php'; ?>