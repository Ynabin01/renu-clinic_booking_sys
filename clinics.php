<?php
$pageTitle = 'Clinics';
$basePath = '';

require 'includes/header.php';
require 'config/database.php';

$clinics = $pdo->query(
    'SELECT * FROM clinics ORDER BY clinic_name'
)->fetchAll();

$doctors = $pdo->query(
    'SELECT d.*, c.clinic_name
     FROM doctors d
     JOIN clinics c ON d.clinic_id = c.clinic_id
     ORDER BY c.clinic_name, d.doctor_name'
)->fetchAll();
?>

<section class="clinic-page">

    <!-- =========================
         HERO
    ========================== -->

    <div class="clinic-hero">

        <div class="hero-content">

            <div class="hero-label">
                <span></span>
                Healthcare & Appointments
            </div>

            <h1>Find the Right Clinic<br>for Your Healthcare Needs</h1>

            <p>
                Explore our available clinics, view healthcare professionals,
                and book your appointment quickly and easily.
            </p>

        </div>

    </div>


    <!-- =========================
         QUICK STATS
    ========================== -->

    <div class="quick-stats">

        <div class="stat-box">

            <div class="stat-icon">✚</div>

            <div>
                <span class="stat-number">
                    <?= count($clinics) ?>
                </span>

                <span class="stat-label">
                    Available Clinics
                </span>
            </div>

        </div>


        <div class="stat-box">

            <div class="stat-icon">Dr</div>

            <div>
                <span class="stat-number">
                    <?= count($doctors) ?>
                </span>

                <span class="stat-label">
                    Healthcare Professionals
                </span>
            </div>

        </div>

    </div>


    <div class="clinic-container">

        <!-- =========================
             CLINICS
        ========================== -->

        <div class="section-heading">

            <div class="heading-left">

                <h2>Available Clinics</h2>

                <p>
                    Choose a clinic and schedule your appointment.
                </p>

            </div>

            <div class="heading-count">
                <?= count($clinics) ?> Available
            </div>

        </div>


        <div class="clinic-grid">

            <?php foreach ($clinics as $clinic): ?>

                <div class="clinic-card-modern">

                    <div class="clinic-top">

                        <div class="clinic-symbol">
                            ✚
                        </div>

                        <div class="clinic-status">
                            <i></i>
                            Available
                        </div>

                    </div>


                    <h3>
                        <?= htmlspecialchars($clinic['clinic_name']) ?>
                    </h3>


                    <div class="clinic-details">

                        <div class="detail-row">

                            <div class="detail-icon">⌖</div>

                            <div>
                                <?= htmlspecialchars($clinic['address']) ?>
                            </div>

                        </div>


                        <div class="detail-row">

                            <div class="detail-icon">☎</div>

                            <div>
                                <?= htmlspecialchars($clinic['phone']) ?>
                            </div>

                        </div>


                        <div class="detail-row">

                            <div class="detail-icon">@</div>

                            <div>
                                <?= htmlspecialchars($clinic['email']) ?>
                            </div>

                        </div>

                    </div>


                    <a class="book-btn" href="patient/book.php?clinic_id=<?= $clinic['clinic_id'] ?>">
                        Book Appointment
                        <span>→</span>
                    </a>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- =========================
             DOCTORS
        ========================== -->

        <div class="doctors-section">

            <div class="section-heading">

                <div class="heading-left">

                    <h2>Meet Our Doctors</h2>

                    <p>
                        Experienced professionals available through our clinics.
                    </p>

                </div>

                <div class="heading-count">
                    <?= count($doctors) ?> Doctors
                </div>

            </div>


            <div class="doctor-grid">

                <?php if (!$doctors): ?>

                    <div class="empty-state">
                        No doctors have been added yet.
                    </div>

                <?php else: ?>

                    <?php foreach ($doctors as $d): ?>

                        <div class="doctor-card-modern">

                            <div class="doctor-profile">

                                <div class="doctor-avatar-modern">
                                    Dr
                                </div>

                                <div>

                                    <h3 class="doctor-name">
                                        <?= htmlspecialchars($d['doctor_name']) ?>
                                    </h3>

                                    <p class="doctor-specialty-modern">
                                        <?= htmlspecialchars($d['specialization']) ?>
                                    </p>

                                </div>

                            </div>


                            <div class="doctor-divider"></div>


                            <div class="doctor-meta">

                                <div class="doctor-meta-icon">
                                    ✚
                                </div>

                                <span>
                                    <?= htmlspecialchars($d['clinic_name']) ?>
                                </span>

                            </div>


                            <?php if ($d['phone']): ?>

                                <div class="doctor-meta">

                                    <div class="doctor-meta-icon">
                                        ☎
                                    </div>

                                    <span>
                                        <?= htmlspecialchars($d['phone']) ?>
                                    </span>

                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>


<?php require 'includes/footer.php'; ?>