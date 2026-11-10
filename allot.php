
<?php
include "db.php";

$message = "";
$message_type = "";

$student_id = "";
$student_name = "";
$course = "";
$phone = "";
$room_no = "";
$allot_date = "";
$academic_year = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = trim($_POST['student_id'] ?? '');
    $student_name = trim($_POST['student_name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $room_no = trim($_POST['room_no'] ?? '');
    $allot_date = trim($_POST['allot_date'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');

    if (
        $student_id == "" ||
        $student_name == "" ||
        $course == "" ||
        $phone == "" ||
        $room_no == "" ||
        $allot_date == "" ||
        $academic_year == ""
    ) {

        $message = "Please fill in all the fields.";
        $message_type = "error";

    } else {

        try {

            // Start database transaction
            mysqli_begin_transaction($conn);
            // Check room capacity before allotment
$room_check_sql = "SELECT capacity
                   FROM room
                   WHERE room_no = ?
                   FOR UPDATE";

$room_check_stmt = mysqli_prepare($conn, $room_check_sql);

mysqli_stmt_bind_param($room_check_stmt, "s", $room_no);
mysqli_stmt_execute($room_check_stmt);

mysqli_stmt_bind_result($room_check_stmt, $room_capacity);

if (!mysqli_stmt_fetch($room_check_stmt)) {
    mysqli_stmt_close($room_check_stmt);
    throw new Exception("Selected room does not exist.");
}

mysqli_stmt_close($room_check_stmt);

// Count students already allotted to this room
$count_sql = "SELECT COUNT(*)
              FROM allotment
              WHERE room_no = ?";

$count_stmt = mysqli_prepare($conn, $count_sql);

mysqli_stmt_bind_param($count_stmt, "s", $room_no);
mysqli_stmt_execute($count_stmt);

mysqli_stmt_bind_result($count_stmt, $occupied);

mysqli_stmt_fetch($count_stmt);
mysqli_stmt_close($count_stmt);

// Prevent allotment if room is full
if ($occupied >= $room_capacity) {
    throw new Exception("Sorry! This room is already full. Please select another room.");
}

            // Insert new student
            $student_sql = "INSERT INTO student
                            (student_id, name, course, phone)
                            VALUES (?, ?, ?, ?)";

            $student_stmt = mysqli_prepare($conn, $student_sql);

            if (!$student_stmt) {
                throw new Exception("Student query preparation failed.");
            }

            mysqli_stmt_bind_param(
                $student_stmt,
                "ssss",
                $student_id,
                $student_name,
                $course,
                $phone
            );

            mysqli_stmt_execute($student_stmt);
            mysqli_stmt_close($student_stmt);

            // Insert room allotment
            $allot_sql = "INSERT INTO allotment
                          (student_id, room_no, allot_date, academic_year)
                          VALUES (?, ?, ?, ?)";

            $allot_stmt = mysqli_prepare($conn, $allot_sql);

            if (!$allot_stmt) {
                throw new Exception("Allotment query preparation failed.");
            }

            mysqli_stmt_bind_param(
                $allot_stmt,
                "ssss",
                $student_id,
                $room_no,
                $allot_date,
                $academic_year
            );

            mysqli_stmt_execute($allot_stmt);
            mysqli_stmt_close($allot_stmt);

            // Save both insertions
            mysqli_commit($conn);

            $message = "Hostel room allotted successfully!";
            $message_type = "success";

            // Clear form
            $student_id = "";
            $student_name = "";
            $course = "";
            $phone = "";
            $room_no = "";
            $allot_date = "";
            $academic_year = "";

        } catch (mysqli_sql_exception $e) {

            mysqli_rollback($conn);

            if ($e->getCode() == 1062) {
                $message = "This Student ID already exists. Please enter a new Student ID.";
            } else {
                $message = "Unable to save the student or allotment. Please check the details.";
            }

            $message_type = "error";

        } catch (Exception $e) {

    mysqli_rollback($conn);

    $message = $e->getMessage();
    $message_type = "error";
}
    }
}

// Retrieve rooms for dropdown
$rooms = mysqli_query(
    $conn,
    "SELECT
        r.room_no,
        r.block,
        r.room_type,
        r.capacity,
        (r.capacity - COUNT(a.allot_id)) AS remaining
     FROM room r
     LEFT JOIN allotment a ON r.room_no = a.room_no
     GROUP BY r.room_no, r.block, r.room_type, r.capacity
     ORDER BY r.room_no ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Hostel Room Allotment</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    color: #29291f;
    min-height: 100vh;
    background: #e9e1d5;
}

/* Main hero background */

.hero {
    min-height: 100vh;
    position: relative;
    display: flex;
    flex-direction: column;
    overflow: hidden;

    background-image:
        linear-gradient(
            90deg,
            rgba(211, 197, 174, 0.88) 0%,
            rgba(211, 197, 174, 0.76) 27%,
            rgba(211, 197, 174, 0.30) 53%,
            rgba(35, 37, 25, 0.18) 100%
        ),
        linear-gradient(
            0deg,
            rgba(25, 28, 19, 0.28),
            transparent 45%
        ),
        url("images/hostel new-bg.png");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* Navigation */

.navbar {
    width: 100%;
    min-height: 85px;
    padding: 25px 5.5%;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    position: relative;
    z-index: 2;
}

.brand {
    font-size: 19px;
    font-weight: 600;
    letter-spacing: 5px;
    color: #29291f;
    text-decoration: none;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-links a {
    text-decoration: none;
    color: #fffdf7;
    background: #29301f;
    padding: 15px 23px;
    font-size: 11px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    transition: 0.3s;
}

.nav-links a:hover {
    background: #465039;
}

/* Active page button */
.nav-links a.active-button {
    background: #fffdf7;
    color: #29301f;
    border: 1px solid #29301f;
}

.nav-links a.active-button:hover {
    background: #eee8dc;
}

/* Hero content */

.hero-content {
    width: 100%;
    padding: 25px 5.5% 0;
    position: relative;
    z-index: 1;
}

.eyebrow {
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #686657;
    margin-bottom: 23px;
}

.hero-content h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(48px, 6.3vw, 82px);
    font-weight: 400;
    line-height: 1.12;
    letter-spacing: -2px;
    max-width: 650px;
    color: #24251d;
}

.hero-line {
    width: 105px;
    height: 1px;
    background: #64634f;
    margin: 28px 0 20px;
}

.hero-description {
    font-size: 13px;
    color: #555548;
    letter-spacing: 0.3px;
    line-height: 1.8;
    max-width: 390px;
}

/* Allotment booking strip */

.booking-section {
    width: 89%;
    margin: auto auto 75px;
    margin-top: 30px;
    position: relative;
    z-index: 2;
}

.booking-heading {
    color: #fffdf7;
    font-size: 11px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    margin-bottom: 13px;
    text-shadow: 0 1px 5px rgba(0,0,0,0.35);
}

.booking-bar {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    background: rgba(226, 216, 199, 0.96);
    box-shadow: 0 8px 28px rgba(25, 25, 18, 0.16);
    backdrop-filter: blur(10px);
}

.submit-btn {
    grid-column: span 4;
    min-height: 55px;
}

.form-field {
    min-width: 0;
    padding: 13px 19px;
    border-right: 1px solid #ddd8ce;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: transparent;
}

.form-field label {
    font-size: 10px;
    letter-spacing: 1.1px;
    text-transform: uppercase;
    color: #777365;
    margin-bottom: 9px;
}

.form-field input,
.form-field select {
    width: 100%;
    height: 29px;
    border: none;
    outline: none;
    background: transparent;
    color: #343429;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
}

.form-field select {
    cursor: pointer;
}

.form-field input:focus,
.form-field select:focus {
    color: #65704b;
}

.submit-btn {
    border: none;
    background: #29301f;
    color: #fffdf7;
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    letter-spacing: 1.7px;
    text-transform: uppercase;
    cursor: pointer;
    transition: 0.3s;
    padding: 15px;
}

.submit-btn:hover {
    background: #465039;
}

/* Status messages */

.message {
    padding: 13px 18px;
    margin-bottom: 14px;
    font-size: 13px;
    background: #fffdf7;
    box-shadow: 0 3px 12px rgba(0,0,0,0.10);
}

.success {
    color: #385c39;
    border-left: 4px solid #66845c;
}

.error {
    color: #9a3939;
    border-left: 4px solid #b65d52;
}

/* Bottom links */

.bottom-links {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 19px;
}

.bottom-links p {
    font-size: 11px;
    color: #fffdf7;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
}

.report-link {
    color: #fffdf7;
    text-decoration: none;
    font-size: 11px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    border-bottom: 1px solid rgba(255,255,255,0.7);
    padding-bottom: 5px;
    transition: 0.3s;
}

.report-link:hover {
    color: #e8d7b7;
    border-color: #e8d7b7;
}

/* Responsive layout */

@media (max-width: 900px) {
    .booking-section {
        width: 89%;
        margin-top: 70px;
    }

    .booking-bar {
        grid-template-columns: 1fr 1fr;
    }

    .form-field {
        border-bottom: 1px solid #ddd8ce;
    }

    .submit-btn {
        min-height: 60px;
        grid-column: span 2;
    }

    .hero {
        min-height: 100vh;
    }

    .hero-content {
        padding-top: 50px;
    }
}

@media (max-width: 600px) {
    .navbar {
        padding: 22px 6%;
        min-height: 70px;
    }

    .brand {
        font-size: 15px;
        letter-spacing: 3px;
    }

    .nav-links {
        gap: 13px;
    }

    .nav-links a {
        font-size: 9px;
    }

    .nav-links a:first-child {
        display: none;
    }

    .nav-links .nav-button {
        padding: 12px;
    }

    .hero-content {
        padding: 55px 7% 0;
    }

    .hero-content h1 {
        font-size: 49px;
        letter-spacing: -1.5px;
    }

    .booking-section {
        width: 86%;
        margin-top: 65px;
        margin-bottom: 35px;
    }

    .booking-bar {
        grid-template-columns: 1fr;
    }

    .form-field {
        padding: 12px 16px;
        min-height: 75px;
        border-right: none;
    }

    .submit-btn {
        grid-column: auto;
    }

    .bottom-links {
        gap: 15px;
        flex-wrap: wrap;
    }

    .hero {
        background-position: 60% center;
    }
}
</style>
</head>

<body>

<div class="hero">

    <!-- Navigation -->
    <nav class="navbar">

    <div class="nav-links">

        <a href="allot.php" class="active-button">
            ALLOT A ROOM
        </a>

        <a href="report.php">
            ALLOTMENTS
        </a>

    </div>

</nav>

    <!-- Main heading -->
    <section class="hero-content">

        <p class="eyebrow">A place to belong</p>

        <h1>
            Your stay,<br>
            your space.
        </h1>

        <div class="hero-line"></div>

        <p class="hero-description">
            A comfortable place to call home.
            Explore your accommodation and complete
            your hostel room allotment with ease.
        </p>

    </section>

    <!-- Allotment form -->
    <section class="booking-section" id="allotment">

        <p class="booking-heading">Hostel Room Allotment</p>

        <?php if ($message != "") { ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <form method="POST" action="allot.php" class="booking-bar">

    <!-- Student ID -->
    <div class="form-field">
        <label for="student_id">Student ID</label>

        <input
            type="text"
            name="student_id"
            id="student_id"
            placeholder="Enter Student ID"
            value="<?php echo htmlspecialchars($student_id); ?>"
            maxlength="20"
            required
        >
    </div>

    <!-- Student Name -->
    <div class="form-field">
        <label for="student_name">Student Name</label>

        <input
            type="text"
            name="student_name"
            id="student_name"
            placeholder="Enter Student Name"
            value="<?php echo htmlspecialchars($student_name); ?>"
            maxlength="100"
            required
        >
    </div>

    <!-- Course -->
    <div class="form-field">
        <label for="course">Course</label>

        <input
            type="text"
            name="course"
            id="course"
            placeholder="Enter Course"
            value="<?php echo htmlspecialchars($course); ?>"
            maxlength="100"
            required
        >
    </div>

    <!-- Phone Number -->
    <div class="form-field">
        <label for="phone">Phone Number</label>

        <input
            type="tel"
            name="phone"
            id="phone"
            placeholder="Enter Phone Number"
            value="<?php echo htmlspecialchars($phone); ?>"
            maxlength="10"
            required
        >
    </div>

    <!-- Room Number -->
    <div class="form-field">
        <label for="room_no">Room Number</label>

        <select name="room_no" id="room_no" required>
            <option value="">Select Room</option>

            <?php if ($rooms) { ?>
    <?php while ($row = mysqli_fetch_assoc($rooms)) { ?>

        <option
            value="<?php echo htmlspecialchars($row['room_no']); ?>"
            <?php echo ($room_no == $row['room_no']) ? 'selected' : ''; ?>
            <?php echo ((int)$row['remaining'] <= 0) ? 'disabled' : ''; ?>
        >

            <?php echo htmlspecialchars($row['room_no']); ?>
            - <?php echo htmlspecialchars($row['block']); ?>

            <?php if ((int)$row['remaining'] > 0) { ?>

                (<?php echo (int)$row['remaining']; ?> spaces left)

            <?php } else { ?>

                (FULL)

            <?php } ?>

        </option>

    <?php } ?>
<?php } ?>

        </select>
    </div>

    <!-- Allotment Date -->
    <div class="form-field">
        <label for="allot_date">Allotment Date</label>

        <input
            type="date"
            name="allot_date"
            id="allot_date"
            value="<?php echo htmlspecialchars($allot_date); ?>"
            required
        >
    </div>

    <!-- Academic Year -->
    <div class="form-field">
        <label for="academic_year">Academic Year</label>

        <input
            type="text"
            name="academic_year"
            id="academic_year"
            placeholder="2026-2027"
            value="<?php echo htmlspecialchars($academic_year); ?>"
            pattern="[0-9]{4}-[0-9]{4}"
            title="Enter academic year in the format 2026-2027"
            required
        >
    </div>

    <!-- Submit -->
    <button type="submit" class="submit-btn">
         Allot Hostel Room
    </button>

</form>
        <div class="bottom-links">
            <p>Hostel Room Allotment System</p>

            <a href="report.php" class="report-link">
                View Allotment Report →
            </a>
        </div>

    </section>

</div>

</body>
</html>
