<?php
$pageTitle = 'Book Appointment';
$basePath = '../';

require '../includes/header.php';
require '../config/database.php';

if (empty($_SESSION['patient_id'])) {
  header('Location: login.php');
  exit;
}

$clinics = $pdo->query(
  'SELECT * FROM clinics ORDER BY clinic_name'
)->fetchAll();

$doctors = $pdo->query(
  'SELECT doctor_id, clinic_id, doctor_name, specialization
     FROM doctors
     ORDER BY doctor_name'
)->fetchAll();

$error = '';
$success = '';

$oldClinic = (int) ($_POST['clinic_id'] ?? $_GET['clinic_id'] ?? 0);
$oldDoctor = (int) ($_POST['doctor_id'] ?? 0);
$oldDate = $_POST['appointment_date'] ?? '';
$oldTime = $_POST['appointment_time'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $clinic = $oldClinic;
  $doctor = $oldDoctor;
  $date = $oldDate;
  $time = $oldTime;

  if (!$clinic || !$doctor || !$date || !$time) {

    $error = 'Please select clinic, doctor, date and time.';

  } elseif ($date < date('Y-m-d', strtotime('+1 day'))) {

    $error = 'Please select a future date.';

  } else {

    $doctorCheck = $pdo->prepare(
      'SELECT doctor_id, doctor_name, specialization
             FROM doctors
             WHERE doctor_id = ? AND clinic_id = ?'
    );

    $doctorCheck->execute([
      $doctor,
      $clinic
    ]);

    $doctorData = $doctorCheck->fetch();

    if (!$doctorData) {

      $error =
        'The selected doctor does not belong to the selected clinic.';

    } else {

      $check = $pdo->prepare(
        "SELECT appointment_id
                 FROM appointments
                 WHERE clinic_id = ?
                 AND doctor_id = ?
                 AND appointment_date = ?
                 AND appointment_time = ?
                 AND status IN ('Pending', 'Confirmed')
                 LIMIT 1"
      );

      $check->execute([
        $clinic,
        $doctor,
        $date,
        $time
      ]);

      if ($check->fetch()) {

        $error =
          'This time is already occupied. Please choose another available time.';

      } else {

        try {

          $stmt = $pdo->prepare(
            'INSERT INTO appointments
                        (
                            patient_id,
                            clinic_id,
                            doctor_id,
                            appointment_date,
                            appointment_time
                        )
                        VALUES (?, ?, ?, ?, ?)'
          );

          $stmt->execute([
            $_SESSION['patient_id'],
            $clinic,
            $doctor,
            $date,
            $time
          ]);

          $success =
            'Your appointment request has been submitted successfully.';

          $oldTime = '';

        } catch (PDOException $e) {

          $error =
            'This appointment time has just been taken. Please choose another available time.';
        }
      }
    }
  }
}
?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/patient-booking.css">

<section class="patientbooking-page">

  <div class="patientbooking-container">

    <!-- =====================================================
             TOP HEADER
        ====================================================== -->

    <div class="patientbooking-top">

      <div>

        <div class="patientbooking-breadcrumb">
          Patient Portal
          <span>/</span>
          Book Appointment
        </div>

        <h1>
          Book an Appointment
        </h1>

        <p>
          Select your clinic, doctor and preferred appointment time.
          We will check availability before your request is submitted.
        </p>

      </div>

      <a href="appointments.php" class="patientbooking-myappointments">
        <span class="patientbooking-myappointments-icon">
          ↗
        </span>

        <span>
          <small>APPOINTMENTS</small>
          My Appointments
        </span>
      </a>

    </div>


    <!-- =====================================================
             ALERTS
        ====================================================== -->

    <?php if ($success): ?>

      <div class="patientbooking-alert patientbooking-alert-success">

        <div class="patientbooking-alert-symbol">
          ✓
        </div>

        <div>
          <strong>
            Appointment submitted
          </strong>

          <p>
            <?= htmlspecialchars($success) ?>
          </p>
        </div>

        <a href="appointments.php">
          View appointments →
        </a>

      </div>

    <?php endif; ?>


    <?php if ($error): ?>

      <div class="patientbooking-alert patientbooking-alert-error">

        <div class="patientbooking-alert-symbol">
          !
        </div>

        <div>
          <strong>
            Appointment could not be submitted
          </strong>

          <p>
            <?= htmlspecialchars($error) ?>
          </p>
        </div>

      </div>

    <?php endif; ?>


    <!-- =====================================================
             BOOKING LAYOUT
        ====================================================== -->

    <div class="patientbooking-layout">

      <!-- =================================================
                 MAIN FORM
            ================================================== -->

      <main class="patientbooking-card">

        <div class="patientbooking-card-top">

          <div>

            <span class="patientbooking-section-label">
              APPOINTMENT DETAILS
            </span>

            <h2>
              Schedule your visit
            </h2>

          </div>

          <span class="patientbooking-required">
            * Required fields
          </span>

        </div>


        <form method="post" id="bookingForm" class="patientbooking-form">

          <!-- =============================================
                         CLINIC
                    ============================================== -->

          <div class="patientbooking-form-section">

            <div class="patientbooking-field-heading">

              <span class="patientbooking-number">
                01
              </span>

              <div>

                <strong>
                  Select a clinic
                </strong>

                <span>
                  Choose the clinic where you want your appointment.
                </span>

              </div>

            </div>


            <div class="patientbooking-field">

              <label>
                Clinic
                <b>*</b>
              </label>


              <!-- Hidden field used by PHP -->

              <input type="hidden" name="clinic_id" id="clinicSelect" value="<?= $oldClinic ?>">


              <!-- Custom dropdown -->

              <div class="patientbooking-dropdown" id="clinicDropdown">

                <button type="button" class="patientbooking-dropdown-trigger" id="clinicTrigger">

                  <span class="patientbooking-dropdown-selected">

                    <?php
                    $selectedClinicName = '';
                    $selectedClinicAddress = '';

                    foreach ($clinics as $clinicItem) {
                      if (
                        (int) $clinicItem['clinic_id'] ===
                        $oldClinic
                      ) {
                        $selectedClinicName =
                          $clinicItem['clinic_name'];

                        $selectedClinicAddress =
                          $clinicItem['address'] ?? '';

                        break;
                      }
                    }
                    ?>

                    <?php if ($selectedClinicName): ?>

                      <span class="patientbooking-selected-title">
                        <?= htmlspecialchars($selectedClinicName) ?>
                      </span>

                      <?php if ($selectedClinicAddress): ?>

                        <span class="patientbooking-selected-subtitle">
                          <?= htmlspecialchars($selectedClinicAddress) ?>
                        </span>

                      <?php endif; ?>

                    <?php else: ?>

                      <span class="patientbooking-placeholder">
                        Select a clinic
                      </span>

                    <?php endif; ?>

                  </span>

                  <span class="patientbooking-chevron">
                    ⌄
                  </span>

                </button>


                <div class="patientbooking-dropdown-menu" id="clinicMenu">

                  <div class="patientbooking-search">

                    <span>⌕</span>

                    <input type="text" id="clinicSearch" placeholder="Search clinics..." autocomplete="off">

                  </div>


                  <div class="patientbooking-dropdown-options" id="clinicOptions">

                    <?php foreach ($clinics as $c): ?>

                      <button type="button"
                        class="patientbooking-option <?= $oldClinic == $c['clinic_id'] ? 'selected' : '' ?>"
                        data-value="<?= (int) $c['clinic_id'] ?>" data-name="<?= htmlspecialchars($c['clinic_name']) ?>"
                        data-address="<?= htmlspecialchars($c['address'] ?? '') ?>">

                        <span class="patientbooking-option-icon">
                          +
                        </span>

                        <span class="patientbooking-option-text">

                          <strong>
                            <?= htmlspecialchars($c['clinic_name']) ?>
                          </strong>

                          <?php if (!empty($c['address'])): ?>

                            <small>
                              <?= htmlspecialchars($c['address']) ?>
                            </small>

                          <?php endif; ?>

                        </span>

                        <span class="patientbooking-option-check">
                          ✓
                        </span>

                      </button>

                    <?php endforeach; ?>

                  </div>

                </div>

              </div>

            </div>

          </div>


          <!-- =============================================
                         DOCTOR
                    ============================================== -->

          <div class="patientbooking-form-section">

            <div class="patientbooking-field-heading">

              <span class="patientbooking-number">
                02
              </span>

              <div>

                <strong>
                  Select a doctor
                </strong>

                <span>
                  Choose from the doctors available at your selected clinic.
                </span>

              </div>

            </div>


            <div class="patientbooking-field">

              <label>
                Doctor
                <b>*</b>
              </label>


              <input type="hidden" name="doctor_id" id="doctorSelect" value="<?= $oldDoctor ?>">


              <div class="patientbooking-dropdown disabled" id="doctorDropdown">

                <button type="button" class="patientbooking-dropdown-trigger" id="doctorTrigger">

                  <span class="patientbooking-dropdown-selected">

                    <span class="patientbooking-placeholder" id="doctorPlaceholder">
                      Select a clinic first
                    </span>

                  </span>

                  <span class="patientbooking-chevron">
                    ⌄
                  </span>

                </button>


                <div class="patientbooking-dropdown-menu" id="doctorMenu">

                  <div class="patientbooking-search">

                    <span>⌕</span>

                    <input type="text" id="doctorSearch" placeholder="Search doctors..." autocomplete="off">

                  </div>


                  <div class="patientbooking-dropdown-options" id="doctorOptions"></div>

                </div>

              </div>


              <div id="doctorInfo" class="patientbooking-doctor-info">
                Select a clinic to view its doctors.
              </div>

            </div>

          </div>


          <!-- =============================================
                         DATE / TIME
                    ============================================== -->

          <div class="patientbooking-form-section">

            <div class="patientbooking-field-heading">

              <span class="patientbooking-number">
                03
              </span>

              <div>

                <strong>
                  Choose date and time
                </strong>

                <span>
                  Select a future date and check available time slots.
                </span>

              </div>

            </div>


            <div class="patientbooking-date-time">

              <div class="patientbooking-field">

                <label for="dateSelect">
                  Appointment date
                  <b>*</b>
                </label>

                <div class="patientbooking-input-wrap">

                  <span class="patientbooking-input-icon">
                    ▣
                  </span>

                  <input type="date" name="appointment_date" id="dateSelect"
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= htmlspecialchars($oldDate) ?>" required>

                </div>

              </div>


              <div class="patientbooking-field">

                <label for="timeSelect">
                  Preferred time
                  <b>*</b>
                </label>

                <div class="patientbooking-input-wrap">

                  <span class="patientbooking-input-icon">
                    ◷
                  </span>

                  <input type="time" name="appointment_time" id="timeSelect" value="<?= htmlspecialchars($oldTime) ?>"
                    required>

                </div>

              </div>

            </div>


            <!-- AVAILABILITY -->

            <div id="availabilityBox" class="patientbooking-availability">

              <div class="patientbooking-availability-status"></div>

              <div>

                <strong>
                  Appointment availability
                </strong>

                <p class="availability-message">
                  Select a doctor and date to check available times.
                </p>

              </div>

            </div>


            <!-- AVAILABLE TIMES -->

            <div id="alternativeTimes" class="patientbooking-alternatives"></div>

          </div>


          <!-- =============================================
                         SUBMIT
                    ============================================== -->

          <div class="patientbooking-submit-row">

            <div class="patientbooking-security">

              <span>
                ✓
              </span>

              <p>
                Your appointment time will be checked again
                before the request is saved.
              </p>

            </div>


            <button type="submit" id="submitBtn" class="patientbooking-submit">
              Submit Appointment
              <span>→</span>
            </button>

          </div>

        </form>

      </main>


      <!-- =================================================
                 RIGHT PANEL
            ================================================== -->

      <aside class="patientbooking-side">


        <!-- SUMMARY -->

        <div class="patientbooking-summary">

          <div class="patientbooking-summary-header">

            <span>
              YOUR APPOINTMENT
            </span>

            <h3>
              Booking Summary
            </h3>

          </div>


          <div class="patientbooking-summary-body">

            <div class="patientbooking-summary-row">

              <div class="patientbooking-summary-icon">
                +
              </div>

              <div>

                <small>
                  CLINIC
                </small>

                <strong id="summaryClinic">
                  Not selected
                </strong>

              </div>

            </div>


            <div class="patientbooking-summary-row">

              <div class="patientbooking-summary-icon">
                Dr
              </div>

              <div>

                <small>
                  DOCTOR
                </small>

                <strong id="summaryDoctor">
                  Not selected
                </strong>

              </div>

            </div>


            <div class="patientbooking-summary-row">

              <div class="patientbooking-summary-icon">
                ▣
              </div>

              <div>

                <small>
                  DATE
                </small>

                <strong id="summaryDate">
                  Not selected
                </strong>

              </div>

            </div>


            <div class="patientbooking-summary-row">

              <div class="patientbooking-summary-icon">
                ◷
              </div>

              <div>

                <small>
                  TIME
                </small>

                <strong id="summaryTime">
                  Not selected
                </strong>

              </div>

            </div>

          </div>


          <div id="summaryStatus" class="patientbooking-summary-status">
            Complete the appointment details to check availability.
          </div>

        </div>


        <!-- INFORMATION -->

        <div class="patientbooking-information">

          <div class="patientbooking-information-title">

            <span>
              APPOINTMENT PROCESS
            </span>

            <h3>
              How booking works
            </h3>

          </div>


          <div class="patientbooking-process">

            <div class="patientbooking-process-item">

              <span>01</span>

              <div>
                <strong>Choose a clinic</strong>

                <p>
                  Select the clinic that is convenient for you.
                </p>
              </div>

            </div>


            <div class="patientbooking-process-item">

              <span>02</span>

              <div>
                <strong>Choose your doctor</strong>

                <p>
                  View doctors available at that clinic.
                </p>
              </div>

            </div>


            <div class="patientbooking-process-item">

              <span>03</span>

              <div>
                <strong>Check availability</strong>

                <p>
                  Available appointment times are shown automatically.
                </p>
              </div>

            </div>


            <div class="patientbooking-process-item">

              <span>04</span>

              <div>
                <strong>Submit your request</strong>

                <p>
                  The clinic will receive your appointment request.
                </p>
              </div>

            </div>

          </div>

        </div>


        <!-- HELP -->

        <div class="patientbooking-help">

          <div class="patientbooking-help-icon">
            ?
          </div>

          <div>

            <strong>
              Need another time?
            </strong>

            <p>
              If your preferred time is occupied, available
              alternatives will appear automatically.
            </p>

          </div>

        </div>

      </aside>

    </div>

  </div>

</section>


<script>

  const clinicDoctors =
    <?= json_encode(
      $doctors,
      JSON_HEX_TAG |
      JSON_HEX_APOS |
      JSON_HEX_QUOT |
      JSON_HEX_AMP
    ) ?>;


  /* =========================================================
     ELEMENTS
  ========================================================= */

  const clinicSelect =
    document.getElementById('clinicSelect');

  const doctorSelect =
    document.getElementById('doctorSelect');

  const clinicDropdown =
    document.getElementById('clinicDropdown');

  const doctorDropdown =
    document.getElementById('doctorDropdown');

  const clinicTrigger =
    document.getElementById('clinicTrigger');

  const doctorTrigger =
    document.getElementById('doctorTrigger');

  const clinicMenu =
    document.getElementById('clinicMenu');

  const doctorMenu =
    document.getElementById('doctorMenu');

  const clinicSearch =
    document.getElementById('clinicSearch');

  const doctorSearch =
    document.getElementById('doctorSearch');

  const clinicOptions =
    document.getElementById('clinicOptions');

  const doctorOptions =
    document.getElementById('doctorOptions');

  const doctorInfo =
    document.getElementById('doctorInfo');

  const doctorPlaceholder =
    document.getElementById('doctorPlaceholder');

  const dateSelect =
    document.getElementById('dateSelect');

  const timeSelect =
    document.getElementById('timeSelect');

  const availabilityBox =
    document.getElementById('availabilityBox');

  const alternativeTimes =
    document.getElementById('alternativeTimes');

  const submitBtn =
    document.getElementById('submitBtn');

  const bookingForm =
    document.getElementById('bookingForm');

  const summaryClinic =
    document.getElementById('summaryClinic');

  const summaryDoctor =
    document.getElementById('summaryDoctor');

  const summaryDate =
    document.getElementById('summaryDate');

  const summaryTime =
    document.getElementById('summaryTime');

  const summaryStatus =
    document.getElementById('summaryStatus');

  const oldDoctor =
    <?= (int) $oldDoctor ?>;


  /* =========================================================
     ESCAPE
  ========================================================= */

  function escapeHtml(value) {

    return String(value).replace(
      /[&<>'"]/g,
      character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#039;',
        '"': '&quot;'
      }[character])
    );
  }


  /* =========================================================
     DROPDOWN OPEN / CLOSE
  ========================================================= */

  function closeAllDropdowns(except = null) {

    document
      .querySelectorAll('.patientbooking-dropdown.open')
      .forEach(dropdown => {

        if (dropdown !== except) {
          dropdown.classList.remove('open');
        }

      });
  }


  clinicTrigger.addEventListener(
    'click',
    function (event) {

      event.stopPropagation();

      if (clinicDropdown.classList.contains('disabled')) {
        return;
      }

      const wasOpen =
        clinicDropdown.classList.contains('open');

      closeAllDropdowns();

      if (!wasOpen) {

        clinicDropdown.classList.add('open');

        setTimeout(() => {
          clinicSearch.focus();
        }, 50);
      }
    }
  );


  doctorTrigger.addEventListener(
    'click',
    function (event) {

      event.stopPropagation();

      if (doctorDropdown.classList.contains('disabled')) {
        return;
      }

      const wasOpen =
        doctorDropdown.classList.contains('open');

      closeAllDropdowns();

      if (!wasOpen) {

        doctorDropdown.classList.add('open');

        setTimeout(() => {
          doctorSearch.focus();
        }, 50);
      }
    }
  );


  document.addEventListener(
    'click',
    function () {
      closeAllDropdowns();
    }
  );


  clinicMenu.addEventListener(
    'click',
    function (event) {
      event.stopPropagation();
    }
  );


  doctorMenu.addEventListener(
    'click',
    function (event) {
      event.stopPropagation();
    }
  );


  /* =========================================================
     CLINIC SEARCH
  ========================================================= */

  clinicSearch.addEventListener(
    'input',
    function () {

      const query =
        this.value.toLowerCase().trim();

      clinicOptions
        .querySelectorAll('.patientbooking-option')
        .forEach(option => {

          const text =
            option.textContent.toLowerCase();

          option.style.display =
            text.includes(query)
              ? 'flex'
              : 'none';
        });
    }
  );


  /* =========================================================
     DOCTOR SEARCH
  ========================================================= */

  doctorSearch.addEventListener(
    'input',
    function () {

      const query =
        this.value.toLowerCase().trim();

      doctorOptions
        .querySelectorAll('.patientbooking-option')
        .forEach(option => {

          const text =
            option.textContent.toLowerCase();

          option.style.display =
            text.includes(query)
              ? 'flex'
              : 'none';
        });
    }
  );


  /* =========================================================
     SELECT CLINIC
  ========================================================= */

  function selectClinic(option) {

    const value =
      option.dataset.value;

    const name =
      option.dataset.name;

    const address =
      option.dataset.address;


    clinicSelect.value = value;


    const selectedContainer =
      clinicTrigger.querySelector(
        '.patientbooking-dropdown-selected'
      );


    selectedContainer.innerHTML = `

        <span class="patientbooking-selected-title">
            ${escapeHtml(name)}
        </span>

        ${address
        ? `
                    <span class="patientbooking-selected-subtitle">
                        ${escapeHtml(address)}
                    </span>
                  `
        : ''
      }

    `;


    clinicOptions
      .querySelectorAll('.patientbooking-option')
      .forEach(item => {

        item.classList.remove('selected');

      });


    option.classList.add('selected');

    clinicDropdown.classList.remove('open');

    clinicSearch.value = '';

    clinicOptions
      .querySelectorAll('.patientbooking-option')
      .forEach(item => {
        item.style.display = 'flex';
      });


    /*
     * Reset doctor after clinic changes.
     */
    doctorSelect.value = '';

    resetDoctorDropdown();

    loadDoctors();

    updateSummary();

    checkAvailability();
  }


  /* =========================================================
     SELECT DOCTOR
  ========================================================= */

  function selectDoctor(option) {

    const value =
      option.dataset.value;

    const name =
      option.dataset.name;

    const specialization =
      option.dataset.specialization;


    doctorSelect.value = value;


    doctorPlaceholder.outerHTML = `

        <span class="patientbooking-selected-title"
              id="doctorPlaceholder">

            ${escapeHtml(name)}

        </span>

        <span class="patientbooking-selected-subtitle">

            ${escapeHtml(specialization)}

        </span>

    `;


    doctorOptions
      .querySelectorAll('.patientbooking-option')
      .forEach(item => {

        item.classList.remove('selected');

      });


    option.classList.add('selected');

    doctorDropdown.classList.remove('open');

    doctorSearch.value = '';

    doctorOptions
      .querySelectorAll('.patientbooking-option')
      .forEach(item => {
        item.style.display = 'flex';
      });


    showDoctor();

    updateSummary();

    checkAvailability();
  }


  /* =========================================================
     CLINIC OPTION EVENTS
  ========================================================= */

  clinicOptions.addEventListener(
    'click',
    function (event) {

      const option =
        event.target.closest(
          '.patientbooking-option'
        );

      if (!option) {
        return;
      }

      selectClinic(option);
    }
  );


  /* =========================================================
     DOCTOR OPTION EVENTS
  ========================================================= */

  doctorOptions.addEventListener(
    'click',
    function (event) {

      const option =
        event.target.closest(
          '.patientbooking-option'
        );

      if (!option) {
        return;
      }

      selectDoctor(option);
    }
  );


  /* =========================================================
     RESET DOCTOR
  ========================================================= */

  function resetDoctorDropdown() {

    doctorDropdown.classList.add('disabled');

    doctorTrigger.innerHTML = `

        <span class="patientbooking-dropdown-selected">

            <span
                class="patientbooking-placeholder"
                id="doctorPlaceholder"
            >
                Select a clinic first
            </span>

        </span>

        <span class="patientbooking-chevron">
            ⌄
        </span>

    `;

    doctorOptions.innerHTML = '';

    doctorInfo.textContent =
      'Select a clinic to view its doctors.';
  }


  /* =========================================================
     LOAD DOCTORS
  ========================================================= */

  function loadDoctors() {

    const clinicId =
      clinicSelect.value;

    doctorOptions.innerHTML = '';


    if (!clinicId) {

      resetDoctorDropdown();

      return;
    }


    const list =
      clinicDoctors.filter(
        doctor =>
          String(doctor.clinic_id) ===
          String(clinicId)
      );


    if (!list.length) {

      doctorDropdown.classList.add('disabled');

      doctorTrigger.innerHTML = `

            <span class="patientbooking-dropdown-selected">

                <span class="patientbooking-placeholder">
                    No doctors available
                </span>

            </span>

            <span class="patientbooking-chevron">
                ⌄
            </span>
        `;


      doctorInfo.textContent =
        'No doctor has been added for this clinic yet. Please contact the clinic.';

      return;
    }


    doctorDropdown.classList.remove('disabled');


    list.forEach(doctor => {

      const option =
        document.createElement('button');

      option.type = 'button';

      option.className =
        'patientbooking-option';

      if (
        String(doctor.doctor_id) ===
        String(oldDoctor)
      ) {
        option.classList.add('selected');
      }


      option.dataset.value =
        doctor.doctor_id;

      option.dataset.name =
        doctor.doctor_name;

      option.dataset.specialization =
        doctor.specialization;


      option.innerHTML = `

            <span class="patientbooking-option-icon doctor">
                Dr
            </span>

            <span class="patientbooking-option-text">

                <strong>
                    ${escapeHtml(doctor.doctor_name)}
                </strong>

                <small>
                    ${escapeHtml(doctor.specialization)}
                </small>

            </span>

            <span class="patientbooking-option-check">
                ✓
            </span>

        `;


      doctorOptions.appendChild(option);
    });


    /*
     * Restore previous doctor after POST.
     */
    if (oldDoctor) {

      const selected =
        list.find(
          doctor =>
            String(doctor.doctor_id) ===
            String(oldDoctor)
        );

      if (selected) {

        doctorSelect.value =
          selected.doctor_id;


        doctorTrigger.innerHTML = `

                <span class="patientbooking-dropdown-selected">

                    <span
                        class="patientbooking-selected-title"
                        id="doctorPlaceholder"
                    >
                        ${escapeHtml(selected.doctor_name)}
                    </span>

                    <span class="patientbooking-selected-subtitle">
                        ${escapeHtml(selected.specialization)}
                    </span>

                </span>

                <span class="patientbooking-chevron">
                    ⌄
                </span>

            `;
      }
    }


    showDoctor();
  }


  /* =========================================================
     SHOW DOCTOR
  ========================================================= */

  function showDoctor() {

    const doctor =
      clinicDoctors.find(
        item =>
          String(item.doctor_id) ===
          String(doctorSelect.value)
      );


    if (doctor) {

      doctorInfo.innerHTML = `

            <span class="patientbooking-doctor-check">
                ✓
            </span>

            <strong>
                ${escapeHtml(doctor.doctor_name)}
            </strong>

            <span>
                ${escapeHtml(doctor.specialization)}
            </span>

        `;

    } else {

      doctorInfo.textContent =
        'Select a doctor to continue.';
    }
  }


  /* =========================================================
     SUMMARY
  ========================================================= */

  function updateSummary() {

    const clinicOption =
      clinicOptions.querySelector(
        '.patientbooking-option.selected'
      );

    const doctorOption =
      doctorOptions.querySelector(
        '.patientbooking-option.selected'
      );


    summaryClinic.textContent =
      clinicOption
        ? clinicOption.dataset.name
        : 'Not selected';


    summaryDoctor.textContent =
      doctorOption
        ? doctorOption.dataset.name
        : 'Not selected';


    if (dateSelect.value) {

      const date =
        new Date(
          dateSelect.value + 'T00:00:00'
        );

      summaryDate.textContent =
        date.toLocaleDateString(
          undefined,
          {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
          }
        );

    } else {

      summaryDate.textContent =
        'Not selected';
    }


    if (timeSelect.value) {

      const [hours, minutes] =
        timeSelect.value.split(':');

      const date =
        new Date();

      date.setHours(
        parseInt(hours),
        parseInt(minutes),
        0
      );

      summaryTime.textContent =
        date.toLocaleTimeString(
          undefined,
          {
            hour: 'numeric',
            minute: '2-digit'
          }
        );

    } else {

      summaryTime.textContent =
        'Not selected';
    }
  }


  /* =========================================================
     SUMMARY STATUS
  ========================================================= */

  function setSummaryStatus(
    message,
    type = ''
  ) {

    summaryStatus.className =
      'patientbooking-summary-status ' + type;

    summaryStatus.textContent =
      message;
  }


  /* =========================================================
     RESET AVAILABILITY
  ========================================================= */

  function resetAvailability(message) {

    availabilityBox.className =
      'patientbooking-availability';

    availabilityBox.querySelector(
      '.availability-message'
    ).textContent = message;

    alternativeTimes.innerHTML = '';

    submitBtn.disabled = false;

    setSummaryStatus(
      'Complete the appointment details to check availability.'
    );
  }


  /* =========================================================
     CHECK AVAILABILITY
  ========================================================= */

  function checkAvailability() {

    const doctor =
      doctorSelect.value;

    const date =
      dateSelect.value;


    alternativeTimes.innerHTML = '';


    if (!doctor || !date) {

      resetAvailability(
        'Select a doctor and date to check available times.'
      );

      updateSummary();

      return;
    }


    availabilityBox.className =
      'patientbooking-availability checking';

    availabilityBox.querySelector(
      '.availability-message'
    ).textContent =
      'Checking available appointment times...';


    setSummaryStatus(
      'Checking appointment availability...',
      'checking'
    );


    submitBtn.disabled = false;


    fetch(
      'availability.php?doctor_id=' +
      encodeURIComponent(doctor) +
      '&date=' +
      encodeURIComponent(date),
      {
        cache: 'no-store'
      }
    )

      .then(response => response.json())

      .then(data => {

        if (!data.ok) {

          throw new Error(
            data.message ||
            'Unable to check availability.'
          );
        }


        availabilityBox.className =
          'patientbooking-availability available';

        availabilityBox.querySelector(
          '.availability-message'
        ).innerHTML =
          '<strong>Availability checked.</strong> ' +
          'Choose an available time below or enter your preferred time.';


        setSummaryStatus(
          'Availability checked. Your selected time will be verified again before booking.',
          'available'
        );


        if (
          Array.isArray(data.available) &&
          data.available.length
        ) {

          alternativeTimes.innerHTML = `

                <div class="patientbooking-alternative-header">

                    <div>

                        <strong>
                            Available times
                        </strong>

                        <span>
                            Select a time to use it for your appointment.
                        </span>

                    </div>

                </div>

                <div class="patientbooking-time-grid">

                    ${data.available
              .slice(0, 12)
              .map(time => `

                                <button
                                    type="button"
                                    class="patientbooking-time"
                                    data-time="${escapeHtml(time)}"
                                >
                                    ${escapeHtml(time)}
                                </button>

                            `)
              .join('')
            }

                </div>
            `;


          alternativeTimes
            .querySelectorAll(
              '.patientbooking-time'
            )
            .forEach(button => {

              button.addEventListener(
                'click',
                function () {

                  timeSelect.value =
                    this.dataset.time;


                  alternativeTimes
                    .querySelectorAll(
                      '.patientbooking-time'
                    )
                    .forEach(item => {

                      item.classList.remove(
                        'selected'
                      );

                    });


                  this.classList.add(
                    'selected'
                  );


                  updateSummary();

                  checkSelectedTime();
                }
              );

            });
        }


        checkSelectedTime();

      })

      .catch(() => {

        availabilityBox.className =
          'patientbooking-availability';

        availabilityBox.querySelector(
          '.availability-message'
        ).textContent =
          'Availability could not be checked right now. '
          + 'The server will verify your appointment before saving.';

        submitBtn.disabled = false;

        setSummaryStatus(
          'Availability will be verified when you submit.',
          'checking'
        );
      });
  }


  /* =========================================================
     CHECK SELECTED TIME
  ========================================================= */

  function checkSelectedTime() {

    const doctor =
      doctorSelect.value;

    const date =
      dateSelect.value;

    const time =
      timeSelect.value;


    updateSummary();


    if (!doctor || !date || !time) {

      submitBtn.disabled = false;

      return;
    }


    fetch(
      'availability.php?doctor_id=' +
      encodeURIComponent(doctor) +
      '&date=' +
      encodeURIComponent(date) +
      '&time=' +
      encodeURIComponent(time),
      {
        cache: 'no-store'
      }
    )

      .then(response => response.json())

      .then(data => {

        if (!data.ok) {
          return;
        }


        if (data.selected_occupied) {

          availabilityBox.className =
            'patientbooking-availability occupied';

          availabilityBox.querySelector(
            '.availability-message'
          ).innerHTML =
            '<strong>This time is already occupied.</strong> ' +
            'Please choose another available time.';

          submitBtn.disabled = true;

          setSummaryStatus(
            'The selected time is occupied. Please choose another time.',
            'occupied'
          );

        } else {

          availabilityBox.className =
            'patientbooking-availability available';

          availabilityBox.querySelector(
            '.availability-message'
          ).innerHTML =
            '<strong>Your selected time is available.</strong> ' +
            'You can submit your appointment request.';

          submitBtn.disabled = false;

          setSummaryStatus(
            'Your selected appointment time is available.',
            'available'
          );
        }

      })

      .catch(() => {

        submitBtn.disabled = false;
      });
  }


  /* =========================================================
     DATE / TIME EVENTS
  ========================================================= */

  dateSelect.addEventListener(
    'change',
    function () {

      updateSummary();

      checkAvailability();
    }
  );


  timeSelect.addEventListener(
    'change',
    function () {

      updateSummary();

      checkSelectedTime();
    }
  );


  /* =========================================================
     FORM SUBMIT
  ========================================================= */

  bookingForm.addEventListener(
    'submit',
    function (event) {

      if (submitBtn.disabled) {

        event.preventDefault();

        return;
      }


      submitBtn.disabled = true;

      submitBtn.innerHTML =
        'Submitting appointment <span>...</span>';
    }
  );


  /* =========================================================
     INITIALIZATION
  ========================================================= */

  if (clinicSelect.value) {

    loadDoctors();

  } else {

    resetDoctorDropdown();
  }


  updateSummary();

</script>

<?php require '../includes/footer.php'; ?>