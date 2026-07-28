<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_forms.php';

if (!form_request_method_is_post()) {
    form_json_response(false, 'Invalid request method.', 405);
}

if (!form_rate_limit('contact')) {
    form_json_response(false, 'Please wait before submitting again.', 429);
}

$name = form_value('name', 150);
$phone = form_value('phone', 30);
$email = form_value('email', 191);
$subject = form_value('subject', 100);
$message = form_textarea_value('message');
$consentAccepted = isset($_POST['consent_accepted']) ? 1 : 0;

$errors = [];
if ($name === '') {
    $errors[] = 'Full name is required.';
}
if ($phone === '') {
    $errors[] = 'Phone number is required.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email address is invalid.';
}
if ($consentAccepted !== 1) {
    $errors[] = 'Consent is required.';
}
if ($errors !== []) {
    form_json_response(false, implode(' ', $errors), 422);
}

try {
    $subjectId = form_find_contact_subject_id($subject);
    $storedMessage = $subjectId === null ? form_message_with_context($message, ['Subject' => $subject]) : $message;

    $stmt = db()->prepare(
        'INSERT INTO contact_inquiries
         (full_name, phone, email, subject_id, message, consent_accepted, consent_at, status, source_page, ip_hash, user_agent)
         VALUES
         (:full_name, :phone, :email, :subject_id, :message, 1, CURRENT_TIMESTAMP, "new", :source_page, :ip_hash, :user_agent)'
    );
    $stmt->execute([
        ':full_name' => $name,
        ':phone' => $phone,
        ':email' => $email !== '' ? $email : null,
        ':subject_id' => $subjectId,
        ':message' => $storedMessage,
        ':source_page' => 'contact.html',
        ':ip_hash' => form_ip_hash(),
        ':user_agent' => form_user_agent(),
    ]);

    form_json_response(true, 'Thank you. Your message has been sent.');
} catch (Throwable $exception) {
    form_json_response(false, 'Unable to submit your message right now. Please call the clinic directly.', 500);
}
