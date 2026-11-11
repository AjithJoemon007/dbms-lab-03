# Hostel Room Allotment System

## Project Details

- **Name:** Ajith E Joemon
- **Roll Number:** 03
- **Question Number:** Q3
- **Project:** Hostel Room Allotment System

## Project Description

The Hostel Room Allotment System is a PHP and MySQL-based web application designed to register students and manage hostel room allotments. It allows users to enter new student details, select available rooms and track room capacity. The system also generates an allotment report using database JOIN operations.

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- XAMPP
- phpMyAdmin

## Main Features

- New student registration
- Hostel room selection
- Automatic room capacity management
- Student and room allotment database management
- Allotment report with student details
- Database integration using PHP and MySQL

## Database Tables

1. **student** – Stores student ID, name, course and phone number.
2. **room** – Stores room number, block, room type and capacity.
3. **allotment** – Stores student ID, room number, allotment date and academic year.

## PHP and MySQL Implementation

1. The `db.php` file establishes a connection between PHP and the MySQL database using `mysqli_connect()`.
2. The `allot.php` page accepts new student details through an HTML form.
3. PHP prepared statements execute INSERT queries to store student and room allotment information in the database.
4. Database transactions ensure that student registration and room allotment are saved together.
5. The `report.php` page executes a SELECT query using INNER JOIN operations on the three tables.
6. The retrieved records are displayed in a structured allotment report.

## Project Files

- `db.php` – Database connection
- `allot.php` – Student registration and room allotment
- `report.php` – Allotment report
- `hostel_db.sql` – Database structure and sample records
- `images/` – Background images
- `screenshots/` – Project screenshots

## How to Run the Project

1. Install XAMPP and start Apache and MySQL.
2. Copy the project folder into `htdocs`.
3. Open phpMyAdmin and import `hostel_db.sql`.
4. Update database connection details in `db.php` if required.
5. Open `http://localhost/hostel_allotment/allot.php` in your browser.

## Screenshots

Project screenshots are available in the `screenshots` folder.
