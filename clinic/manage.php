<?php
$pageTitle = 'Manage Clinics & Doctors';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

if (empty($_SESSION['clinic_admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';
$selectedClinic = (int)($_GET['clinic_id'] ?? 0);

if (isset($_GET['added'])) {
    $message = 'Clinic added successfully. You can now add doctors for it.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    try {

        /* =====================================================
           UPDATE CLINIC
           ===================================================== */
        if ($action === 'update_clinic') {

            $id = (int)($_POST['clinic_id'] ?? 0);
            $name = trim($_POST['clinic_name'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($id < 1 || $name === '' || $address === '') {
                throw new Exception('Clinic name and address are required.');
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid clinic email.');
            }

            $stmt = $pdo->prepare(
                'UPDATE clinics
                 SET clinic_name=?, address=?, phone=?, email=?
                 WHERE clinic_id=?'
            );

            $stmt->execute([
                $name,
                $address,
                $phone,
                $email,
                $id
            ]);

            $selectedClinic = $id;
            $message = 'Clinic details updated successfully.';
        }


        /* =====================================================
           ADD DOCTOR
           ===================================================== */
        elseif ($action === 'add_doctor') {

            $clinicId = (int)($_POST['clinic_id'] ?? 0);
            $doctorName = trim($_POST['doctor_name'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');
            $phone = trim($_POST['doctor_phone'] ?? '');
            $email = trim($_POST['doctor_email'] ?? '');

            if (
                $clinicId < 1 ||
                $doctorName === '' ||
                $specialization === ''
            ) {
                throw new Exception(
                    'Doctor name and specialization are required.'
                );
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid doctor email.');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO doctors
                 (clinic_id, doctor_name, specialization, phone, email)
                 VALUES (?,?,?,?,?)'
            );

            $stmt->execute([
                $clinicId,
                $doctorName,
                $specialization,
                $phone,
                $email
            ]);

            $selectedClinic = $clinicId;
            $message = 'Doctor added successfully.';
        }


        /* =====================================================
           DELETE DOCTOR
           ===================================================== */
        elseif ($action === 'delete_doctor') {

            $doctorId = (int)($_POST['doctor_id'] ?? 0);
            $clinicId = (int)($_POST['clinic_id'] ?? 0);

            if ($doctorId < 1) {
                throw new Exception('Invalid doctor selected.');
            }

            $stmt = $pdo->prepare(
                'DELETE FROM doctors WHERE doctor_id=?'
            );

            $stmt->execute([$doctorId]);

            $selectedClinic = $clinicId;
            $message = 'Doctor removed successfully.';
        }


        /* =====================================================
           DELETE CLINIC
           ===================================================== */
        elseif ($action === 'delete_clinic') {

            $clinicId = (int)($_POST['clinic_id'] ?? 0);

            if ($clinicId < 1) {
                throw new Exception('Invalid clinic selected.');
            }

            /*
             * Check whether this clinic still has doctors.
             * We do not automatically delete doctors because
             * accidental deletion of doctor records should be avoided.
             */
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM doctors WHERE clinic_id=?'
            );

            $stmt->execute([$clinicId]);

            $doctorCount = (int)$stmt->fetchColumn();

            if ($doctorCount > 0) {
                throw new Exception(
                    'This clinic cannot be removed because it still has '
                    . $doctorCount
                    . ' doctor(s). Please remove the doctors first.'
                );
            }

            /*
             * Confirm clinic exists before deleting.
             */
            $stmt = $pdo->prepare(
                'SELECT clinic_name FROM clinics WHERE clinic_id=?'
            );

            $stmt->execute([$clinicId]);

            $clinicToDelete = $stmt->fetch();

            if (!$clinicToDelete) {
                throw new Exception('Clinic not found.');
            }

            /*
             * Delete clinic.
             */
            $stmt = $pdo->prepare(
                'DELETE FROM clinics WHERE clinic_id=?'
            );

            $stmt->execute([$clinicId]);

            /*
             * Select another clinic after deletion.
             */
            $selectedClinic = 0;

            $message =
                'Clinic "' .
                htmlspecialchars($clinicToDelete['clinic_name']) .
                '" was removed successfully.';
        }

    } catch (Throwable $e) {

        $error = $e->getMessage();
    }
}


/* =========================================================
   LOAD CLINICS
   ========================================================= */

$clinics = $pdo
    ->query('SELECT * FROM clinics ORDER BY clinic_name')
    ->fetchAll();


/*
 * If selected clinic no longer exists,
 * automatically select the first available clinic.
 */
if ($selectedClinic) {

    $selectedExists = false;

    foreach ($clinics as $item) {

        if ((int)$item['clinic_id'] === $selectedClinic) {
            $selectedExists = true;
            break;
        }
    }

    if (!$selectedExists) {
        $selectedClinic = 0;
    }
}

if (!$selectedClinic && $clinics) {
    $selectedClinic = (int)$clinics[0]['clinic_id'];
}


/* =========================================================
   LOAD SELECTED CLINIC
   ========================================================= */

$clinic = null;

foreach ($clinics as $item) {

    if ((int)$item['clinic_id'] === $selectedClinic) {
        $clinic = $item;
        break;
    }
}


/* =========================================================
   LOAD DOCTORS
   ========================================================= */

$doctors = [];

if ($clinic) {

    $stmt = $pdo->prepare(
        'SELECT * FROM doctors
         WHERE clinic_id=?
         ORDER BY doctor_name'
    );

    $stmt->execute([$selectedClinic]);

    $doctors = $stmt->fetchAll();
}
?>

<section class="clinicmanage-page">

    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <div class="clinicmanage-header">

        <div class="clinicmanage-header-content">

            <span class="clinicmanage-eyebrow">
                ADMINISTRATION
            </span>

            <h1>Manage Clinics & Doctors</h1>

            <p>
                Manage clinic information, doctors and clinic availability
                from one central place.
            </p>

        </div>


        <div class="clinicmanage-header-actions">

            <a
                href="add_clinic.php"
                class="clinicmanage-primary-btn"
            >
                <span>+</span>
                Add New Clinic
            </a>

            <a
                href="dashboard.php"
                class="clinicmanage-secondary-btn"
            >
                Dashboard
            </a>

        </div>

    </div>


    <!-- =====================================================
         SUCCESS MESSAGE
         ===================================================== -->

    <?php if ($message): ?>

        <div class="clinicmanage-alert clinicmanage-alert-success">

            <div class="clinicmanage-alert-icon">
                ✓
            </div>

            <div>
                <strong>Success</strong>
                <p>
                    <?= $message ?>
                </p>
            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         ERROR MESSAGE
         ===================================================== -->

    <?php if ($error): ?>

        <div class="clinicmanage-alert clinicmanage-alert-error">

            <div class="clinicmanage-alert-icon">
                !
            </div>

            <div>
                <strong>Action could not be completed</strong>

                <p>
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>

        </div>

    <?php endif; ?>


    <?php if (!$clinics): ?>

        <!-- =================================================
             NO CLINICS
             ================================================= -->

        <div class="clinicmanage-empty-page">

            <div class="clinicmanage-empty-icon">
                +
            </div>

            <span class="clinicmanage-eyebrow">
                CLINIC MANAGEMENT
            </span>

            <h2>No Clinics Added Yet</h2>

            <p>
                Start by adding your first clinic. Once a clinic is added,
                you can manage its details and add doctors.
            </p>

            <a
                href="add_clinic.php"
                class="clinicmanage-primary-btn"
            >
                <span>+</span>
                Add First Clinic
            </a>

        </div>


    <?php else: ?>


        <!-- =================================================
             MAIN LAYOUT
             ================================================= -->

        <div class="clinicmanage-layout">


            <!-- =============================================
                 CLINIC SIDEBAR
                 ============================================= -->

            <aside class="clinicmanage-sidebar">

                <div class="clinicmanage-sidebar-head">

                    <div>

                        <span class="clinicmanage-eyebrow">
                            CLINICS
                        </span>

                        <h2>All Clinics</h2>

                    </div>

                    <span class="clinicmanage-count">
                        <?= count($clinics) ?>
                    </span>

                </div>


                <div class="clinicmanage-clinic-list">

                    <?php foreach ($clinics as $item): ?>

                        <?php
                        $itemId = (int)$item['clinic_id'];
                        $isActive = ($itemId === $selectedClinic);
                        ?>

                        <a
                            href="?clinic_id=<?= $itemId ?>"
                            class="clinicmanage-clinic-item <?= $isActive ? 'clinicmanage-active' : '' ?>"
                        >

                            <div class="clinicmanage-clinic-icon">
                                +
                            </div>

                            <div class="clinicmanage-clinic-info">

                                <strong>
                                    <?= htmlspecialchars($item['clinic_name']) ?>
                                </strong>

                                <small>
                                    <?= htmlspecialchars($item['address']) ?>
                                </small>

                            </div>

                            <?php if ($isActive): ?>

                                <span class="clinicmanage-current">
                                    ✓
                                </span>

                            <?php endif; ?>

                        </a>

                    <?php endforeach; ?>

                </div>

            </aside>


            <!-- =============================================
                 MAIN CONTENT
                 ============================================= -->

            <main class="clinicmanage-main">

                <?php if ($clinic): ?>


                    <!-- =====================================
                         CLINIC + DOCTOR FORMS
                         ===================================== -->

                    <div class="clinicmanage-form-grid">


                        <!-- =================================
                             CLINIC DETAILS
                             ================================= -->

                        <div class="clinicmanage-card">

                            <div class="clinicmanage-card-header">

                                <div class="clinicmanage-card-icon clinicmanage-clinic-icon-big">
                                    +
                                </div>

                                <div>

                                    <span class="clinicmanage-eyebrow">
                                        CLINIC INFORMATION
                                    </span>

                                    <h2>Clinic Details</h2>

                                    <p>
                                        Update the selected clinic information.
                                    </p>

                                </div>

                            </div>


                            <form
                                method="POST"
                                class="clinicmanage-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update_clinic"
                                >

                                <input
                                    type="hidden"
                                    name="clinic_id"
                                    value="<?= (int)$clinic['clinic_id'] ?>"
                                >


                                <div class="clinicmanage-field">

                                    <label for="clinic_name">
                                        Clinic Name
                                    </label>

                                    <input
                                        type="text"
                                        id="clinic_name"
                                        name="clinic_name"
                                        value="<?= htmlspecialchars($clinic['clinic_name']) ?>"
                                        required
                                    >

                                </div>


                                <div class="clinicmanage-field">

                                    <label for="address">
                                        Address
                                    </label>

                                    <input
                                        type="text"
                                        id="address"
                                        name="address"
                                        value="<?= htmlspecialchars($clinic['address']) ?>"
                                        required
                                    >

                                </div>


                                <div class="clinicmanage-field-row">

                                    <div class="clinicmanage-field">

                                        <label for="phone">
                                            Phone
                                        </label>

                                        <input
                                            type="text"
                                            id="phone"
                                            name="phone"
                                            value="<?= htmlspecialchars($clinic['phone'] ?? '') ?>"
                                        >

                                    </div>


                                    <div class="clinicmanage-field">

                                        <label for="email">
                                            Email
                                        </label>

                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            value="<?= htmlspecialchars($clinic['email'] ?? '') ?>"
                                        >

                                    </div>

                                </div>


                                <div class="clinicmanage-form-actions">

                                    <button
                                        type="submit"
                                        class="clinicmanage-submit-btn"
                                    >
                                        Save Clinic Changes
                                    </button>


                                    <!-- REMOVE CLINIC -->

                                    <button
                                        type="button"
                                        class="clinicmanage-delete-clinic-btn"
                                        onclick="clinicmanageConfirmDelete(
                                            <?= (int)$clinic['clinic_id'] ?>,
                                            '<?= htmlspecialchars(addslashes($clinic['clinic_name'])) ?>'
                                        )"
                                    >
                                        <span>×</span>
                                        Remove Clinic
                                    </button>

                                </div>

                            </form>

                        </div>


                        <!-- =================================
                             ADD DOCTOR
                             ================================= -->

                        <div class="clinicmanage-card">

                            <div class="clinicmanage-card-header">

                                <div class="clinicmanage-card-icon clinicmanage-doctor-icon-big">
                                    +
                                </div>

                                <div>

                                    <span class="clinicmanage-eyebrow">
                                        MEDICAL STAFF
                                    </span>

                                    <h2>Add Doctor</h2>

                                    <p>
                                        Add a doctor to this clinic.
                                    </p>

                                </div>

                            </div>


                            <form
                                method="POST"
                                class="clinicmanage-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="add_doctor"
                                >

                                <input
                                    type="hidden"
                                    name="clinic_id"
                                    value="<?= (int)$clinic['clinic_id'] ?>"
                                >


                                <div class="clinicmanage-field">

                                    <label for="doctor_name">
                                        Doctor Name
                                    </label>

                                    <input
                                        type="text"
                                        id="doctor_name"
                                        name="doctor_name"
                                        placeholder="Enter doctor name"
                                        required
                                    >

                                </div>


                                <div class="clinicmanage-field">

                                    <label for="specialization">
                                        Specialization
                                    </label>

                                    <input
                                        type="text"
                                        id="specialization"
                                        name="specialization"
                                        placeholder="e.g. General Physician"
                                        required
                                    >

                                </div>


                                <div class="clinicmanage-field-row">

                                    <div class="clinicmanage-field">

                                        <label for="doctor_phone">
                                            Phone
                                        </label>

                                        <input
                                            type="text"
                                            id="doctor_phone"
                                            name="doctor_phone"
                                            placeholder="Phone number"
                                        >

                                    </div>


                                    <div class="clinicmanage-field">

                                        <label for="doctor_email">
                                            Email
                                        </label>

                                        <input
                                            type="email"
                                            id="doctor_email"
                                            name="doctor_email"
                                            placeholder="Email address"
                                        >

                                    </div>

                                </div>


                                <button
                                    type="submit"
                                    class="clinicmanage-submit-btn clinicmanage-doctor-submit"
                                >
                                    Add Doctor
                                </button>

                            </form>

                        </div>

                    </div>


                    <!-- =====================================
                         DOCTORS LIST
                         ===================================== -->

                    <div class="clinicmanage-doctors-card">

                        <div class="clinicmanage-doctors-header">

                            <div>

                                <span class="clinicmanage-eyebrow">
                                    <?= htmlspecialchars($clinic['clinic_name']) ?>
                                </span>

                                <h2>Doctors at This Clinic</h2>

                                <p>
                                    View and manage doctors assigned to this clinic.
                                </p>

                            </div>


                            <div class="clinicmanage-doctor-total">

                                <strong>
                                    <?= count($doctors) ?>
                                </strong>

                                <span>
                                    DOCTORS
                                </span>

                            </div>

                        </div>


                        <?php if ($doctors): ?>

                            <div class="clinicmanage-table-wrapper">

                                <table class="clinicmanage-table">

                                    <thead>

                                        <tr>

                                            <th>Doctor</th>

                                            <th>Specialization</th>

                                            <th>Phone</th>

                                            <th>Email</th>

                                            <th>Action</th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        <?php foreach ($doctors as $doctor): ?>

                                            <tr>

                                                <td>

                                                    <div class="clinicmanage-doctor-cell">

                                                        <div class="clinicmanage-doctor-avatar">
                                                            <?= strtoupper(substr($doctor['doctor_name'], 0, 1)) ?>
                                                        </div>

                                                        <div>

                                                            <strong>
                                                                <?= htmlspecialchars($doctor['doctor_name']) ?>
                                                            </strong>

                                                            <small>
                                                                Doctor
                                                            </small>

                                                        </div>

                                                    </div>

                                                </td>


                                                <td>

                                                    <span class="clinicmanage-specialization">
                                                        <?= htmlspecialchars($doctor['specialization']) ?>
                                                    </span>

                                                </td>


                                                <td>
                                                    <?= htmlspecialchars($doctor['phone'] ?? '') ?>
                                                </td>


                                                <td>
                                                    <?= htmlspecialchars($doctor['email'] ?? '') ?>
                                                </td>


                                                <td>

                                                    <form
                                                        method="POST"
                                                        onsubmit="return clinicmanageConfirmDoctorDelete();"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="delete_doctor"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="doctor_id"
                                                            value="<?= (int)$doctor['doctor_id'] ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="clinic_id"
                                                            value="<?= (int)$clinic['clinic_id'] ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="clinicmanage-remove-btn"
                                                        >
                                                            Remove
                                                        </button>

                                                    </form>

                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <div class="clinicmanage-no-doctors">

                                <div class="clinicmanage-no-doctors-icon">
                                    +
                                </div>

                                <h3>No Doctors Added</h3>

                                <p>
                                    This clinic does not have any doctors yet.
                                    Use the Add Doctor form above to add one.
                                </p>

                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- =====================================
                         HIDDEN DELETE CLINIC FORM
                         ===================================== -->

                    <form
                        method="POST"
                        id="clinicmanage-delete-clinic-form"
                        style="display:none;"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="delete_clinic"
                        >

                        <input
                            type="hidden"
                            name="clinic_id"
                            id="clinicmanage-delete-clinic-id"
                            value=""
                        >

                    </form>


                <?php endif; ?>

            </main>

        </div>

    <?php endif; ?>

</section>


<!-- =========================================================
     DELETE CONFIRMATION
     ========================================================= -->

<script>
function clinicmanageConfirmDelete(clinicId, clinicName) {

    const message =
        'Are you sure you want to remove "' +
        clinicName +
        '"?\n\n' +
        'This clinic can only be removed if it has no doctors.\n\n' +
        'This action cannot be undone.';

    if (confirm(message)) {

        document.getElementById(
            'clinicmanage-delete-clinic-id'
        ).value = clinicId;

        document.getElementById(
            'clinicmanage-delete-clinic-form'
        ).submit();
    }
}


function clinicmanageConfirmDoctorDelete() {

    return confirm(
        'Are you sure you want to remove this doctor?\n\n' +
        'This action cannot be undone.'
    );
}
</script>


<?php require '../includes/footer.php'; ?>