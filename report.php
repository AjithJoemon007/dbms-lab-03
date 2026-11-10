
<?php

include "db.php";

// Retrieve allotment report using JOIN
$sql = "SELECT
            s.student_id,
            s.name,
            s.course,
            r.room_no,
            r.block,
            a.allot_date
        FROM allotment a
        INNER JOIN student s
            ON a.student_id = s.student_id
        INNER JOIN room r
            ON a.room_no = r.room_no
        ORDER BY a.allot_id DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Allotment Report</title>

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
    background: #d3c5ae;
}

/* Main background */

.hero {
    min-height: 100vh;
    padding-bottom: 60px;

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
    background-attachment: fixed;
}

/* Navigation */

.navbar {
    width: 100%;
    min-height: 85px;
    padding: 25px 5.5%;

    display: flex;
    justify-content: flex-end;
    align-items: center;
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

/* Main heading */

.report-heading {
    padding: 45px 5.5% 30px;
}

.eyebrow {
    font-size: 11px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: #686657;
    margin-bottom: 18px;
}

.report-heading h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(45px, 5.5vw, 72px);
    font-weight: 400;
    line-height: 1.15;
    color: #24251d;
}

.heading-line {
    width: 105px;
    height: 1px;
    background: #64634f;
    margin: 22px 0;
}

.report-heading p {
    font-size: 13px;
    line-height: 1.8;
    color: #555548;
}

/* Report container */

.report-container {
    width: 89%;
    margin: 25px auto 0;

    background: rgba(226, 216, 199, 0.94);

    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    padding: 30px;

    box-shadow: 0 10px 35px rgba(25, 25, 18, 0.18);
}

/* Report title */

.report-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;

    margin-bottom: 25px;
}

.report-top h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 27px;
    font-weight: 400;
    color: #303125;
}

.record-count {
    font-size: 11px;
    letter-spacing: 1px;
    color: #65634f;
    text-transform: uppercase;
}

/* Table */

.table-wrapper {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 650px;
}

thead {
    background: #353b2b;
    color: #f8f3e9;
}

th {
    padding: 19px 17px;

    font-size: 11px;
    font-weight: 500;
    letter-spacing: 1.2px;
    text-transform: uppercase;

    text-align: left;
    white-space: nowrap;
}

td {
    padding: 19px 17px;

    font-size: 13px;
    color: #444336;

    border-bottom: 1px solid rgba(91, 85, 66, 0.20);
}

tbody tr {
    transition: 0.25s;
}

tbody tr:nth-child(even) {
    background: rgba(255, 250, 239, 0.25);
}

tbody tr:hover {
    background: rgba(255, 253, 248, 0.60);
}

td:first-child {
    font-weight: 400;
    color: #333728;
}

/* Empty and error messages */

.empty-message,
.error-message {
    padding: 30px;
    text-align: center;
    font-size: 14px;
    color: #625c4b;
}

.error-message {
    color: #963e36;
}

/* Bottom section */

.bottom-section {
    width: 89%;
    margin: 25px auto 0;

    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.bottom-section p {
    font-size: 11px;
    color: #fffdf7;
    letter-spacing: 0.5px;
    text-shadow: 0 1px 5px rgba(0,0,0,0.5);
}

.back-link {
    display: inline-block;
    color: #fffdf7;
    text-decoration: none;

    font-size: 11px;
    letter-spacing: 1.5px;
    text-transform: uppercase;

    border-bottom: 1px solid rgba(255,255,255,0.7);
    padding-bottom: 5px;

    transition: 0.3s;
}

.back-link:hover {
    color: #e8d7b7;
    border-color: #e8d7b7;
}

/* Mobile responsive design */

@media(max-width: 600px) {

    .navbar {
        padding: 20px 6%;
        min-height: 70px;
    }

    .nav-links a {
        padding: 12px 14px;
        font-size: 9px;
    }

    .report-heading {
        padding: 40px 7% 20px;
    }

    .report-heading h1 {
        font-size: 46px;
    }

    .report-container {
        width: 89%;
        padding: 20px 15px;
    }

    .report-top h2 {
        font-size: 23px;
    }

    .bottom-section {
        width: 89%;
    }
}

</style>

</head>

<body>

<div class="hero">

    <!-- Navigation -->

    <nav class="navbar">

        <div class="nav-links">

            <a href="allot.php">
                ALLOT A ROOM
            </a>

            <a href="report.php" class="active-button">
                ALLOTMENTS
            </a>

        </div>

    </nav>


    <!-- Page heading -->

    <section class="report-heading">

        <div class="eyebrow">
            Hostel Accommodation
        </div>

        <h1>
            Allotment<br>
            Records.
        </h1>

        <div class="heading-line"></div>

        <p>
            Explore the room allocation details and
            student accommodation records.
        </p>

    </section>


    <!-- Report table -->

    <section class="report-container">

        <div class="report-top">

            <h2>Room Allocation Details</h2>

            <?php if ($result) { ?>

                <span class="record-count">
                    Total Records: <?php echo mysqli_num_rows($result); ?>
                </span>

            <?php } ?>

        </div>


        <?php if (!$result) { ?>

            <div class="error-message">
                Unable to retrieve the allotment report.
            </div>

        <?php } elseif (mysqli_num_rows($result) > 0) { ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Room No</th>
                            <th>Block</th>
                            <th>Allotment Date</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row['student_id']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['course']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['room_no']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['block']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['allot_date']); ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        <?php } else { ?>

            <div class="empty-message">
                No room allotment records found.
            </div>

        <?php } ?>

    </section>


    <!-- Bottom navigation -->

    <div class="bottom-section">

        <p>Hostel Room Allotment System</p>

        <a href="allot.php" class="back-link">
            ← Back to Room Allotment
        </a>

    </div>

</div>

</body>
</html>
