<?php
$pageTitle = 'My Appointments';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

if (empty($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        a.*,
        c.clinic_name,
        d.doctor_name,
        d.specialization
    FROM appointments a
    JOIN clinics c ON a.clinic_id = c.clinic_id
    LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");

$stmt->execute([$_SESSION['patient_id']]);
$appointments = $stmt->fetchAll();
?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/patient-appointments.css">

<section class="patientappointments-page">

    <div class="patientappointments-container">

        <!-- Page Header -->
        <div class="patientappointments-header">

            <div class="patientappointments-header-content">

                <div>
                    <div class="patientappointments-eyebrow">
                        PATIENT PORTAL
                    </div>

                    <h1>My Appointments</h1>

                    <p>
                        View your upcoming and previous clinic appointments,
                        doctor details, dates, times and current booking status.
                    </p>
                </div>

                <a href="book.php" class="patientappointments-book-btn">
                    <span>+</span>
                    Book New Appointment
                </a>

            </div>

        </div>


        <!-- Appointment Statistics -->
        <div class="patientappointments-stats">

            <div class="patientappointments-stat-card">

                <div class="patientappointments-stat-icon">
                    <span>01</span>
                </div>

                <div>
                    <span class="patientappointments-stat-label">
                        Total Appointments
                    </span>

                    <strong>
                        <?= count($appointments) ?>
                    </strong>
                </div>

            </div>


            <div class="patientappointments-stat-card">

                <div class="patientappointments-stat-icon">
                    <span>02</span>
                </div>

                <div>
                    <span class="patientappointments-stat-label">
                        Pending
                    </span>

                    <strong>
                        <?php
                        $pendingCount = 0;

                        foreach ($appointments as $appointment) {
                            if (strtolower($appointment['status']) === 'pending') {
                                $pendingCount++;
                            }
                        }

                        echo $pendingCount;
                        ?>
                    </strong>
                </div>

            </div>


            <div class="patientappointments-stat-card">

                <div class="patientappointments-stat-icon">
                    <span>03</span>
                </div>

                <div>
                    <span class="patientappointments-stat-label">
                        Confirmed
                    </span>

                    <strong>
                        <?php
                        $confirmedCount = 0;

                        foreach ($appointments as $appointment) {
                            if (strtolower($appointment['status']) === 'confirmed') {
                                $confirmedCount++;
                            }
                        }

                        echo $confirmedCount;
                        ?>
                    </strong>
                </div>

            </div>

        </div>


        <!-- Appointment List -->
        <div class="patientappointments-card">

            <div class="patientappointments-card-header">

                <div>
                    <h2>Appointment History</h2>

                    <p>
                        Your appointment requests and booking information
                        are shown below.
                    </p>
                </div>

                <div class="patientappointments-count">
                    <?= count($appointments) ?>
                    <?= count($appointments) === 1 ? 'Appointment' : 'Appointments' ?>
                </div>

            </div>


            <?php if (!$appointments): ?>

                <!-- Empty State -->
                <div class="patientappointments-empty">

                    <div class="patientappointments-empty-icon">
                        <span>+</span>
                    </div>

                    <h3>No appointments yet</h3>

                    <p>
                        You have not booked any appointments yet.
                        Choose a clinic and doctor to schedule your first appointment.
                    </p>

                    <a href="book.php" class="patientappointments-empty-btn">
                        Book Your First Appointment
                    </a>

                </div>

            <?php else: ?>

                <!-- Desktop Table -->
                <div class="patientappointments-table-wrap">

                    <table class="patientappointments-table">

                        <thead>
                            <tr>
                                <th>Clinic</th>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($appointments as $a): ?>

                                <?php
                                $status = strtolower(trim($a['status'] ?? ''));
                                $statusClass = preg_replace('/[^a-z0-9_-]/', '', $status);

                                $doctorName = $a['doctor_name']
                                    ? $a['doctor_name']
                                    : 'Not selected';

                                $specialization = $a['specialization'] ?? '';

                                $appointmentDate = !empty($a['appointment_date'])
                                    ? date('M d, Y', strtotime($a['appointment_date']))
                                    : 'N/A';

                                $appointmentTime = !empty($a['appointment_time'])
                                    ? date('h:i A', strtotime($a['appointment_time']))
                                    : 'N/A';
                                ?>

                                <tr>

                                    <!-- Clinic -->
                                    <td>

                                        <div class="patientappointments-clinic">

                                            <div class="patientappointments-row-icon">
                                                C
                                            </div>

                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($a['clinic_name']) ?>
                                                </strong>

                                                <span>
                                                    Clinic
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Doctor -->
                                    <td>

                                        <div class="patientappointments-doctor">

                                            <div class="patientappointments-doctor-avatar">
                                                <?= strtoupper(substr($doctorName, 0, 1)) ?>
                                            </div>

                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($doctorName) ?>
                                                </strong>

                                                <?php if ($specialization): ?>

                                                    <span>
                                                        <?= htmlspecialchars($specialization) ?>
                                                    </span>

                                                <?php else: ?>

                                                    <span>
                                                        Doctor information unavailable
                                                    </span>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Date -->
                                    <td>

                                        <div class="patientappointments-date">

                                            <strong>
                                                <?= htmlspecialchars($appointmentDate) ?>
                                            </strong>

                                            <span>
                                                Appointment date
                                            </span>

                                        </div>

                                    </td>


                                    <!-- Time -->
                                    <td>

                                        <div class="patientappointments-time">

                                            <strong>
                                                <?= htmlspecialchars($appointmentTime) ?>
                                            </strong>

                                            <span>
                                                Appointment time
                                            </span>

                                        </div>

                                    </td>


                                    <!-- Status -->
                                    <td>

                                        <span
                                            class="patientappointments-status patientappointments-status-<?= htmlspecialchars($statusClass) ?>">

                                            <span class="patientappointments-status-dot"></span>

                                            <?= htmlspecialchars($a['status']) ?>

                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <!-- Mobile Appointment Cards -->
                <div class="patientappointments-mobile-list">

                    <?php foreach ($appointments as $a): ?>

                        <?php
                        $status = strtolower(trim($a['status'] ?? ''));
                        $statusClass = preg_replace('/[^a-z0-9_-]/', '', $status);

                        $doctorName = $a['doctor_name']
                            ? $a['doctor_name']
                            : 'Not selected';

                        $specialization = $a['specialization'] ?? '';

                        $appointmentDate = !empty($a['appointment_date'])
                            ? date('M d, Y', strtotime($a['appointment_date']))
                            : 'N/A';

                        $appointmentTime = !empty($a['appointment_time'])
                            ? date('h:i A', strtotime($a['appointment_time']))
                            : 'N/A';
                        ?>

                        <div class="patientappointments-mobile-card">

                            <div class="patientappointments-mobile-top">

                                <div>

                                    <span class="patientappointments-mobile-label">
                                        CLINIC
                                    </span>

                                    <h3>
                                        <?= htmlspecialchars($a['clinic_name']) ?>
                                    </h3>

                                </div>

                                <span
                                    class="patientappointments-status patientappointments-status-<?= htmlspecialchars($statusClass) ?>">

                                    <span class="patientappointments-status-dot"></span>

                                    <?= htmlspecialchars($a['status']) ?>

                                </span>

                            </div>


                            <div class="patientappointments-mobile-doctor">

                                <div class="patientappointments-doctor-avatar">
                                    <?= strtoupper(substr($doctorName, 0, 1)) ?>
                                </div>

                                <div>

                                    <strong>
                                        <?= htmlspecialchars($doctorName) ?>
                                    </strong>

                                    <span>
                                        <?= $specialization
                                            ? htmlspecialchars($specialization)
                                            : 'Doctor information unavailable'
                                            ?>
                                    </span>

                                </div>

                            </div>


                            <div class="patientappointments-mobile-details">

                                <div>

                                    <span>Date</span>

                                    <strong>
                                        <?= htmlspecialchars($appointmentDate) ?>
                                    </strong>

                                </div>

                                <div>

                                    <span>Time</span>

                                    <strong>
                                        <?= htmlspecialchars($appointmentTime) ?>
                                    </strong>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<?php require '../includes/footer.php'; ?>