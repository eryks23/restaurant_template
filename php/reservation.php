<?php

// Enable error logging (disable display_errors in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Function to sanitize user input
function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Function to send error response and stop script
function errorResponse(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit(json_encode(['error' => 'Method Not Allowed']));
}

// Get and sanitize POST data
$nameRaw   = $_POST['name'] ?? '';
$emailRaw  = $_POST['email'] ?? '';
$phoneRaw  = $_POST['phone'] ?? '';
$dateRaw   = $_POST['date'] ?? '';
$timeRaw   = $_POST['time'] ?? '';
$guestsRaw = $_POST['guests'] ?? '';
$notesRaw  = $_POST['notes'] ?? '';
$token     = $_POST['g-recaptcha-response'] ?? '';

// Sanitize inputs
$name   = sanitize($nameRaw);
$email  = filter_var(trim($emailRaw), FILTER_SANITIZE_EMAIL);
$phone  = sanitize($phoneRaw);
$date   = sanitize($dateRaw);
$time   = sanitize($timeRaw);
$notes  = sanitize($notesRaw);
$guests = filter_var($guestsRaw, FILTER_VALIDATE_INT);

// Validate required fields
if ($name === '' || $email === '' || $phone === '' || $date === '' || $time === '') {
    errorResponse('All required fields must be filled.');
}

// Validate guests count
if ($guests === false || $guests < 1 || $guests > 20) {
    errorResponse('Number of guests must be between 1 and 20.');
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Invalid email address.');
}

// Validate date (cannot be in the past)
$today = new DateTime('today');
$reservationDate = DateTime::createFromFormat('Y-m-d', $date);
if (!$reservationDate || $reservationDate < $today) {
    errorResponse('Reservation date must be today or later.');
}

// Validate time between 10:00 and 22:00
$timeObj = DateTime::createFromFormat('H:i', $time);
$minTime = DateTime::createFromFormat('H:i', '10:00');
$maxTime = DateTime::createFromFormat('H:i', '22:00');

if (!$timeObj || $timeObj < $minTime || $timeObj > $maxTime) {
    errorResponse('Reservation time must be between 10:00 and 22:00.');
}

// Verify reCAPTCHA
require_once __DIR__ . '/config.php';

$recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
$response = file_get_contents(
    $recaptchaUrl
  . '?secret=' . urlencode(RECAPTCHA_SECRET)
  . '&response=' . urlencode($token)
);

if ($response === false) {
    errorResponse('Failed to verify reCAPTCHA.');
}

$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE || empty($result['success'])) {
    errorResponse('Bot verification failed.', 403);
}

// Connect to the database (mysqli procedural)
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    errorResponse('Database connection failed: ' . mysqli_connect_error(), 500);
}

// Prepare SQL statement to prevent SQL injection
$sql = "INSERT INTO reservations (name, email, phone, date, time, guests, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    mysqli_close($con);
    errorResponse('Database prepare failed: ' . mysqli_error($con), 500);
}

// Bind parameters: s = string, i = integer
mysqli_stmt_bind_param($stmt, "sssssis", $name, $email, $phone, $date, $time, $guests, $notes);

// Execute statement
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    errorResponse('Database execute failed: ' . mysqli_stmt_error($stmt), 500);
}

// Close statement and connection
mysqli_stmt_close($stmt);
mysqli_close($con);

// Send success JSON response
http_response_code(200);
echo json_encode([
    'message' => 'Reservation confirmed',
    'guests' => $guests,
    'notes' => $notes,
    'email' => $email,
    'phone' => $phone
]);
exit;
