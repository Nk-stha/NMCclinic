<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/public_forms.php';

if (!form_request_method_is_post()) {
    form_json_response(false, 'Invalid request method.', 405);
}

if (!form_rate_limit('appointment')) {
    form_json_response(false, 'Please wait before submitting again.', 429);
}

$name = form_value('name', 150);
$phone = form_value('phone', 30);
$service = form_value('service', 150);
$date = form_value('date', 20);
$time = form_value('time', 100);
$message = form_textarea_value('message');
$consentAccepted = isset($_POST['consent_accepted']) ? 1 : 0;

$errors = [];
if ($name === '') {
    $errors[] = 'Full name is required.';
}
if ($phone === '') {
    $errors[] = 'Phone number is required.';
}
if ($date !== '' && !DateTime::createFromFormat('Y-m-d', $date)) {
    $errors[] = 'Preferred date is invalid.';
}
if ($consentAccepted !== 1) {
    $errors[] = 'Consent is required.';
}
if ($errors !== []) {
    form_json_response(false, implode(' ', $errors), 422);
}

try {
    $specialtyId = form_find_specialty_id($service);
    $storedMessage = $specialtyId === null ? form_message_with_context($message, ['Requested service' => $service]) : $message;

    $stmt = db()->prepare(
        'INSERT INTO appointment_requests
         (full_name, phone, specialty_id, preferred_date, preferred_time_slot, message, consent_accepted, consent_at, status, source_page, ip_hash, user_agent)
         VALUES
         (:full_name, :phone, :specialty_id, :preferred_date, :preferred_time_slot, :message, 1, CURRENT_TIMESTAMP, "new", :source_page, :ip_hash, :user_agent)'
    );
    $stmt->execute([
        ':full_name' => $name,
        ':phone' => $phone,
        ':specialty_id' => $specialtyId,
        ':preferred_date' => $date !== '' ? $date : null,
        ':preferred_time_slot' => $time !== '' ? $time : null,
        ':message' => $storedMessage !== '' ? $storedMessage : null,
        ':source_page' => 'index.php#appointment',
        ':ip_hash' => form_ip_hash(),
        ':user_agent' => form_user_agent(),
    ]);

    form_json_response(true, 'Thank you. Your appointment request has been sent.');
} catch (Throwable $exception) {
    form_json_response(false, 'Unable to submit your appointment request right now. Please call the clinic directly.', 500);
}
