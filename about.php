<?php
$pageTitle = 'About';
$basePath = '';

require 'includes/header.php';
?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/about-system.css">

<section class="about-simple-page">

    <div class="about-simple-container">

        <div class="about-simple-header">
            <span>ABOUT THE SYSTEM</span>
            <h1>Online Clinic Appointment Booking System</h1>
            <p>
                A simple web-based system designed to make clinic
                appointment management easier for patients and clinics.
            </p>
        </div>

        <div class="about-simple-card">

            <div class="about-simple-content">

                <h2>About the System</h2>

                <p>
                    The Online Clinic Appointment Booking System is a
                    web-based information system designed to make
                    appointment management easier for patients and clinics.
                </p>

                <p>
                    Patients can register, check clinic information,
                    select doctors and submit appointment requests.
                    Clinic users can manage clinic information, doctors
                    and appointment records through the management area.
                </p>

            </div>

            <div class="about-simple-objectives">

                <h2>Main Objectives</h2>

                <ul>
                    <li>
                        <span>✓</span>
                        Reduce manual appointment handling.
                    </li>

                    <li>
                        <span>✓</span>
                        Make appointment requests easier for patients.
                    </li>

                    <li>
                        <span>✓</span>
                        Help clinics organise appointment information.
                    </li>

                    <li>
                        <span>✓</span>
                        Store appointment data in a structured MySQL database.
                    </li>
                </ul>

            </div>

        </div>

        <div class="about-simple-bottom">

            <div>
                <strong>Simple. Organised. Convenient.</strong>
                <p>
                    Manage clinic appointments in one place.
                </p>
            </div>

            <a href="patient/register.php">
                Get Started →
            </a>

        </div>

    </div>

</section>

<?php require 'includes/footer.php'; ?>