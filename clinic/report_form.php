<?php
$pageTitle = 'Appointment Reports';
$basePath = '../';
require '../includes/header.php';
if (empty($_SESSION['clinic_admin'])) {
    header('Location: login.php');
    exit;
}
$month = date('Y-m');
?>
<section class="section admin-page">
    <div class="page-heading">
        <div><span class="eyebrow">Reports</span>
            <h1>Appointment Report</h1>
            <p class="muted">Select a date range and status. The PDF will contain the matching appointment records.</p>
        </div><a class="btn btn-light" href="dashboard.php">Back to Dashboard</a>
    </div>
    <div class="report-layout">
        <div class="form-card clean-form-card">
            <form method="get" action="report.php" class="clean-form">
                <div class="form-row">
                    <div class="form-group"><label>From Date</label><input type="date" name="from"
                            value="<?= $month ?>-01" required></div>
                    <div class="form-group"><label>To Date</label><input type="date" name="to"
                            value="<?= date('Y-m-t') ?>" required></div>
                </div>
                <div class="form-group"><label>Status</label><select name="status">
                        <option value="All">All statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select></div>
                <button class="btn report-download" type="submit">Download PDF Report</button>
            </form>
        </div>
        <div class="report-help card">
            <div class="report-icon">PDF</div>
            <h2>What's included?</h2>
            <ul>
                <li>Selected date range</li>
                <li>Patient name and phone</li>
                <li>Clinic and doctor</li>
                <li>Appointment date and time</li>
                <li>Current appointment status</li>
                <li>Total matching appointments</li>
            </ul>
        </div>
    </div>
</section>
<?php require '../includes/footer.php'; ?>