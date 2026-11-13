<?php
// The CX Gals contact form handler.
// 1. Put the inbox that should receive enquiries below.
// 2. FROM_EMAIL should be an address on the site's own domain (e.g. noreply@thecxgals.com),
//    otherwise many mail servers will mark the message as spam.

const TO_EMAIL   = 'hello@thecxgals.com';
const FROM_EMAIL = 'noreply@thecxgals.com';

// The page sends the form in the background and expects a short JSON answer.
// Without JavaScript the browser posts normally and is sent back to the page.
function respond($ok) {
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => (bool) $ok]);
    } else {
        header('Location: index.html?sent=' . ($ok ? '1' : '0') . '#call');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Spam trap: bots fill the hidden "website" field, people don't
if (!empty($_POST['website'])) {
    respond(true);
}

$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false);
}

$message = mb_substr($message, 0, 5000);

$subject = 'New CX Gals enquiry from ' . $email;
$body    = "Email: $email\n\nWhat is breaking:\n" . ($message !== '' ? $message : '(left empty)') . "\n";
$headers = [
    'From: The CX Gals website <' . FROM_EMAIL . '>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

$ok = mail(TO_EMAIL, $subject, $body, implode("\r\n", $headers));

respond($ok);
