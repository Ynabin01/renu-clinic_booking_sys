 
<?php

$pageTitle = 'Admin Dashboard';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

if (empty($_SESSION['clinic_admin'])) {
    header('Location: login.php');
    exit;
}


/* Update appointment status */
if (
    isset($_GET['status'], $_GET['id']) &&
    in_array(
        $_GET['status'],
        ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
        true
    )
) {
    $stmt = $pdo->prepare(
        'UPDATE appointments SET status=? WHERE appointment_id=?'
    );

    $stmt->execute([
        $_GET['status'],
        (int) $_GET['id']
    ]);

    header('Location: dashboard.php');
    exit;
}


/* Appointment list */
$appointments = $pdo->query(
    "SELECT
        a.*,
        p.name patient_name,
        c.clinic_name,
        d.doctor_name,
        d.specialization
     FROM appointments a
     JOIN patients p
        ON a.patient_id = p.patient_id
     JOIN clinics c
        ON a.clinic_id = c.clinic_id
     LEFT JOIN doctors d
        ON a.doctor_id = d.doctor_id
     ORDER BY
        a.appointment_date DESC,
        a.appointment_time DESC"
)->fetchAll();


/* Statistics */
$total = $pdo->query(
    'SELECT COUNT(*) FROM appointments'
)->fetchColumn();

$pending = $pdo->query(
    "SELECT COUNT(*) FROM appointments WHERE status='Pending'"
)->fetchColumn();

$confirmed = $pdo->query(
    "SELECT COUNT(*) FROM appointments WHERE status='Confirmed'"
)->fetchColumn();

$completed = $pdo->query(
    "SELECT COUNT(*) FROM appointments WHERE status='Completed'"
)->fetchColumn();

?>

<!-- =====================================================
     CLINIC ADMIN DASHBOARD
     Completely isolated design
     Prefix: cldash-
     ===================================================== -->

<div class="cldash-page">

    <div class="cldash-container">


        <!-- =================================================
             DASHBOARD HEADER
             ================================================= -->

        <div class="cldash-header">

            <div class="cldash-header-left">

                <div class="cldash-header-icon">
                    <span>⌂</span>
                </div>

                <div class="cldash-header-content">

                    <div class="cldash-label">
                        CLINIC ADMINISTRATION
                    </div>

                    <h1>
                        Dashboard
                    </h1>

                    <p>
                        Manage appointments, clinics, doctors and reports from one place.
                    </p>

                </div>

            </div>


            <!-- Header Actions -->
            <div class="cldash-header-actions">

                <a
                    href="manage.php"
                    class="cldash-primary-btn"
                >
                    <span>+</span>
                    Manage Clinics
                </a>

                <a
                    href="report_form.php"
                    class="cldash-secondary-btn"
                >
                    <span>▣</span>
                    PDF Reports (Add in Next Updates)
                </a>

                <a
                    href="../logout.php"
                    class="cldash-logout-btn"
                >
                    <span>↪</span>
                    Logout
                </a>

            </div>

        </div>


        <!-- =================================================
             STATISTICS
             ================================================= -->

        <div class="cldash-stats">


            <!-- Total -->
            <div class="cldash-stat cldash-stat-total">

                <div class="cldash-stat-top">

                    <div class="cldash-stat-icon">
                        #
                    </div>

                    <span class="cldash-stat-label">
                        TOTAL
                    </span>

                </div>

                <div class="cldash-stat-number">
                    <?= $total ?>
                </div>

                <div class="cldash-stat-description">
                    All appointments
                </div>

                <div class="cldash-stat-line"></div>

            </div>


            <!-- Pending -->
            <div class="cldash-stat cldash-stat-pending">

                <div class="cldash-stat-top">

                    <div class="cldash-stat-icon">
                        ◷
                    </div>

                    <span class="cldash-stat-label">
                        PENDING
                    </span>

                </div>

                <div class="cldash-stat-number">
                    <?= $pending ?>
                </div>

                <div class="cldash-stat-description">
                    Waiting for action
                </div>

                <div class="cldash-stat-line"></div>

            </div>


            <!-- Confirmed -->
            <div class="cldash-stat cldash-stat-confirmed">

                <div class="cldash-stat-top">

                    <div class="cldash-stat-icon">
                        ✓
                    </div>

                    <span class="cldash-stat-label">
                        CONFIRMED
                    </span>

                </div>

                <div class="cldash-stat-number">
                    <?= $confirmed ?>
                </div>

                <div class="cldash-stat-description">
                    Upcoming confirmed
                </div>

                <div class="cldash-stat-line"></div>

            </div>


            <!-- Completed -->
            <div class="cldash-stat cldash-stat-completed">

                <div class="cldash-stat-top">

                    <div class="cldash-stat-icon">
                        ✓
                    </div>

                    <span class="cldash-stat-label">
                        COMPLETED
                    </span>

                </div>

                <div class="cldash-stat-number">
                    <?= $completed ?>
                </div>

                <div class="cldash-stat-description">
                    Finished appointments
                </div>

                <div class="cldash-stat-line"></div>

            </div>

        </div>


        <!-- =================================================
             APPOINTMENTS CARD
             ================================================= -->

        <div class="cldash-appointments-card">


            <!-- Card Header -->
            <div class="cldash-card-header">

                <div class="cldash-card-title">

                    <div class="cldash-card-small-label">
                        APPOINTMENT MANAGEMENT
                    </div>

                    <h2>
                        Recent Appointments
                    </h2>

                    <p>
                        Review and update appointment status.
                    </p>

                </div>


                <a
                    href="report_form.php"
                    class="cldash-report-btn"
                >
                    <span>↓</span>
                    Download Report
                </a>

            </div>


            <!-- Table -->
            <div class="cldash-table-wrapper">

                <table class="cldash-table">

                    <thead>

                        <tr>

                            <th>
                                PATIENT
                            </th>

                            <th>
                                CLINIC
                            </th>

                            <th>
                                DOCTOR
                            </th>

                            <th>
                                DATE
                            </th>

                            <th>
                                TIME
                            </th>

                            <th>
                                STATUS
                            </th>

                            <th>
                                ACTION
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (!$appointments): ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="cldash-empty-cell"
                                >

                                    <div class="cldash-empty">

                                        <div class="cldash-empty-icon">
                                            +
                                        </div>

                                        <strong>
                                            No appointments yet
                                        </strong>

                                        <span>
                                            Appointment records will appear here.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($appointments as $a): ?>

                                <tr>

                                    <!-- Patient -->
                                    <td>

                                        <div class="cldash-patient">

                                            <div class="cldash-patient-avatar">
                                                <?= strtoupper(substr($a['patient_name'], 0, 1)) ?>
                                            </div>

                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($a['patient_name']) ?>
                                                </strong>

                                                <small>
                                                    Patient
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Clinic -->
                                    <td>

                                        <span class="cldash-clinic-name">
                                            <?= htmlspecialchars($a['clinic_name']) ?>
                                        </span>

                                    </td>


                                    <!-- Doctor -->
                                    <td>

                                        <?php if ($a['doctor_name']): ?>

                                            <div class="cldash-doctor">

                                                <strong>
                                                    <?= htmlspecialchars($a['doctor_name']) ?>
                                                </strong>

                                                <small>
                                                    <?= htmlspecialchars($a['specialization']) ?>
                                                </small>

                                            </div>

                                        <?php else: ?>

                                            <span class="cldash-not-selected">
                                                Not selected
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Date -->
                                    <td>

                                        <span class="cldash-date">
                                            <?= htmlspecialchars($a['appointment_date']) ?>
                                        </span>

                                    </td>


                                    <!-- Time -->
                                    <td>

                                        <span class="cldash-time">
                                            <?= htmlspecialchars(substr($a['appointment_time'], 0, 5)) ?>
                                        </span>

                                    </td>


                                    <!-- Status -->
                                    <td>

                                        <span class="cldash-status cldash-status-<?= strtolower($a['status']) ?>">

                                            <span class="cldash-status-dot"></span>

                                            <?= htmlspecialchars($a['status']) ?>

                                        </span>

                                    </td>


                                    <!-- Actions -->
                                    <td>

                                        <div class="cldash-actions">

                                            <?php if ($a['status'] !== 'Confirmed'): ?>

                                                <a
                                                    href="?id=<?= $a['appointment_id'] ?>&status=Confirmed"
                                                    class="cldash-action-confirm"
                                                    title="Confirm appointment"
                                                >
                                                    ✓
                                                </a>

                                            <?php endif; ?>


                                            <?php if ($a['status'] !== 'Cancelled'): ?>

                                                <a
                                                    href="?id=<?= $a['appointment_id'] ?>&status=Cancelled"
                                                    class="cldash-action-cancel"
                                                    title="Cancel appointment"
                                                >
                                                    ×
                                                </a>

                                            <?php endif; ?>


                                            <?php if ($a['status'] !== 'Completed'): ?>

                                                <a
                                                    href="?id=<?= $a['appointment_id'] ?>&status=Completed"
                                                    class="cldash-action-complete"
                                                    title="Complete appointment"
                                                >
                                                    ✓
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Footer Note -->
        <div class="cldash-footer-note">

            <span class="cldash-footer-icon">
                i
            </span>

            <span>
                Use the action buttons to update appointment status. Reports can be generated from the PDF Reports section.
            </span>

        </div>

    </div>

</div>


<?php require '../includes/footer.php'; ?>
 
