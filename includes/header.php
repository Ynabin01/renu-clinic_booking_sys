<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Clinic Appointment Booking') ?></title>
<link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/style.css">
<link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/styles.css">
</head>
<body>
<header class="topbar">
  <div class="container nav">
    <a class="brand" href="<?= $basePath ?? '' ?>index.php">ClinicBook</a>
    <nav>
      <a href="<?= $basePath ?? '' ?>index.php">Home</a>
      <a href="<?= $basePath ?? '' ?>about.php">About</a>
      <a href="<?= $basePath ?? '' ?>clinics.php">Clinics</a>
      <?php if (!empty($_SESSION['clinic_admin'])): ?>
        <a href="<?= $basePath ?? '' ?>clinic/dashboard.php">Admin Dashboard</a>
        <a href="<?= $basePath ?? '' ?>logout.php">Logout</a>
      <?php elseif (!empty($_SESSION['patient_id'])): ?>
        <a href="<?= $basePath ?? '' ?>patient/book.php">Book</a>
        <a href="<?= $basePath ?? '' ?>patient/appointments.php">My Appointments</a>
        <a href="<?= $basePath ?? '' ?>logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= $basePath ?? '' ?>patient/login.php">Patient Login</a>
        <a href="<?= $basePath ?? '' ?>clinic/login.php">Staff Login</a>
        <a class="nav-button" href="<?= $basePath ?? '' ?>patient/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
