<?php
declare(strict_types=1);

function doctor_default(): array
{
    return [
        'id' => null,
        'slug' => '',
        'full_name' => '',
        'specialty_id' => '',
        'photo_id' => '',
        'short_bio' => '',
        'nmc_registration_number' => '',
        'status' => 'draft',
        'availability_status' => 'hidden',
        'sort_order' => '0',
        'is_featured' => 0,
    ];
}

function doctor_slug(string $name): string
{
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');

    return $slug !== '' ? $slug : 'doctor';
}

function doctor_specialty_options(): array
{
    $stmt = db()->query(
        "SELECT id, title
         FROM specialties
         WHERE deleted_at IS NULL AND status <> 'archived'
         ORDER BY sort_order ASC, title ASC"
    );

    return $stmt->fetchAll();
}

function doctor_media_options(): array
{
    $stmt = db()->query(
        "SELECT id, file_path, alt_text
         FROM media_assets
         WHERE deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%'
         ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function doctor_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM doctors WHERE id = :id AND deleted_at IS NULL LIMIT 1');
    $stmt->execute([':id' => $id]);
    $doctor = $stmt->fetch();

    return $doctor ?: null;
}

function doctor_qualifications(int $doctorId): array
{
    $stmt = db()->prepare('SELECT qualification, institution, qualification_year FROM doctor_qualifications WHERE doctor_id = :id ORDER BY sort_order ASC, id ASC');
    $stmt->execute([':id' => $doctorId]);

    return $stmt->fetchAll();
}

function doctor_schedules(int $doctorId): array
{
    $stmt = db()->prepare('SELECT day_of_week, start_time, end_time, location_label, appointment_required, is_visible FROM doctor_schedules WHERE doctor_id = :id ORDER BY sort_order ASC, day_of_week ASC');
    $stmt->execute([':id' => $doctorId]);

    return $stmt->fetchAll();
}

function doctor_list(): array
{
    $stmt = db()->query(
        'SELECT d.*, s.title AS specialty_title, m.file_path AS photo_path
         FROM doctors d
         LEFT JOIN specialties s ON s.id = d.specialty_id
         LEFT JOIN media_assets m ON m.id = d.photo_id
         WHERE d.deleted_at IS NULL
         ORDER BY d.sort_order ASC, d.full_name ASC'
    );

    return $stmt->fetchAll();
}

function doctor_from_post(): array
{
    $doctor = doctor_default();
    $doctor['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $doctor['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $doctor['specialty_id'] = trim((string) ($_POST['specialty_id'] ?? ''));
    $doctor['photo_id'] = trim((string) ($_POST['photo_id'] ?? ''));
    $doctor['short_bio'] = trim((string) ($_POST['short_bio'] ?? ''));
    $doctor['nmc_registration_number'] = trim((string) ($_POST['nmc_registration_number'] ?? ''));
    $doctor['status'] = trim((string) ($_POST['status'] ?? 'draft'));
    $doctor['availability_status'] = trim((string) ($_POST['availability_status'] ?? 'hidden'));
    $doctor['sort_order'] = trim((string) ($_POST['sort_order'] ?? '0'));
    $doctor['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;

    if ($doctor['slug'] === '') {
        $doctor['slug'] = doctor_slug($doctor['full_name']);
    } else {
        $doctor['slug'] = doctor_slug($doctor['slug']);
    }

    return $doctor;
}

function doctor_qualifications_from_post(): array
{
    $qualifications = $_POST['qualification'] ?? [];
    $institutions = $_POST['institution'] ?? [];
    $years = $_POST['qualification_year'] ?? [];
    $rows = [];

    if (!is_array($qualifications)) {
        return $rows;
    }

    foreach ($qualifications as $index => $qualification) {
        $qualification = trim((string) $qualification);
        if ($qualification === '') {
            continue;
        }

        $rows[] = [
            'qualification' => $qualification,
            'institution' => trim((string) ($institutions[$index] ?? '')),
            'qualification_year' => trim((string) ($years[$index] ?? '')),
        ];
    }

    return $rows;
}

function doctor_schedules_from_post(): array
{
    $days = $_POST['schedule_day'] ?? [];
    $starts = $_POST['schedule_start'] ?? [];
    $ends = $_POST['schedule_end'] ?? [];
    $locations = $_POST['schedule_location'] ?? [];
    $appointments = $_POST['schedule_appointment_required'] ?? [];
    $visible = $_POST['schedule_visible'] ?? [];
    $rows = [];

    if (!is_array($days)) {
        return $rows;
    }

    foreach ($days as $index => $day) {
        $day = trim((string) $day);
        $start = trim((string) ($starts[$index] ?? ''));
        $end = trim((string) ($ends[$index] ?? ''));
        $location = trim((string) ($locations[$index] ?? ''));

        if ($day === '' && $start === '' && $end === '' && $location === '') {
            continue;
        }

        $rows[] = [
            'day_of_week' => $day,
            'start_time' => $start,
            'end_time' => $end,
            'location_label' => $location,
            'appointment_required' => isset($appointments[$index]) ? 1 : 0,
            'is_visible' => isset($visible[$index]) ? 1 : 0,
        ];
    }

    return $rows;
}

function doctor_validate(array $doctor, array $qualifications, array $schedules, ?int $currentId = null): array
{
    $errors = [];

    if ($doctor['full_name'] === '') {
        $errors[] = 'Doctor full name is required.';
    } elseif (strlen($doctor['full_name']) > 150) {
        $errors[] = 'Doctor full name must be 150 characters or less.';
    }

    if ($doctor['slug'] === '') {
        $errors[] = 'URL slug is required.';
    } elseif (strlen($doctor['slug']) > 191) {
        $errors[] = 'URL slug must be 191 characters or less.';
    }

    if ($doctor['nmc_registration_number'] !== '' && strlen($doctor['nmc_registration_number']) > 100) {
        $errors[] = 'NMC registration number must be 100 characters or less.';
    }

    if (!in_array($doctor['status'], ['draft', 'published', 'archived'], true)) {
        $errors[] = 'Publishing status is invalid.';
    }

    if (!in_array($doctor['availability_status'], ['accepting', 'unavailable', 'hidden'], true)) {
        $errors[] = 'Availability status is invalid.';
    }

    if (!ctype_digit((string) $doctor['sort_order'])) {
        $errors[] = 'Sort order must be a whole number.';
    }

    if ($currentId === null) {
        $stmt = db()->prepare('SELECT id FROM doctors WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $stmt->execute([':slug' => $doctor['slug']]);
    } else {
        $stmt = db()->prepare('SELECT id FROM doctors WHERE slug = :slug AND deleted_at IS NULL AND id <> :id LIMIT 1');
        $stmt->execute([':slug' => $doctor['slug'], ':id' => $currentId]);
    }

    if ($stmt->fetch()) {
        $errors[] = 'This URL slug is already used by another doctor.';
    }

    if ($doctor['specialty_id'] !== '') {
        $stmt = db()->prepare("SELECT id FROM specialties WHERE id = :id AND deleted_at IS NULL AND status <> 'archived' LIMIT 1");
        $stmt->execute([':id' => (int) $doctor['specialty_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected specialty is invalid.';
        }
    }

    if ($doctor['photo_id'] !== '') {
        $stmt = db()->prepare("SELECT id FROM media_assets WHERE id = :id AND deleted_at IS NULL AND is_public = 1 AND mime_type LIKE 'image/%' LIMIT 1");
        $stmt->execute([':id' => (int) $doctor['photo_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected photo is invalid.';
        }
    }

    $maxYear = (int) date('Y') + 1;
    foreach ($qualifications as $qualification) {
        if (strlen($qualification['qualification']) > 255 || strlen($qualification['institution']) > 255) {
            $errors[] = 'Qualification and institution must be 255 characters or less.';
            break;
        }

        if ($qualification['qualification_year'] !== '') {
            $year = (int) $qualification['qualification_year'];
            if (!ctype_digit($qualification['qualification_year']) || $year < 1900 || $year > $maxYear) {
                $errors[] = 'Qualification year is invalid.';
                break;
            }
        }
    }

    foreach ($schedules as $schedule) {
        $day = (int) $schedule['day_of_week'];
        if (!ctype_digit((string) $schedule['day_of_week']) || $day < 0 || $day > 6) {
            $errors[] = 'Schedule day is invalid.';
            break;
        }

        if (($schedule['start_time'] === '') !== ($schedule['end_time'] === '')) {
            $errors[] = 'Schedule start and end time must both be filled.';
            break;
        }

        if (strlen($schedule['location_label']) > 150) {
            $errors[] = 'Schedule location must be 150 characters or less.';
            break;
        }
    }

    return $errors;
}

function doctor_save(array $doctor, array $qualifications, array $schedules, ?int $id = null): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        if ($id === null) {
            $stmt = $pdo->prepare(
                'INSERT INTO doctors
                 (slug, full_name, specialty_id, photo_id, short_bio, nmc_registration_number, status, availability_status, sort_order, is_featured)
                 VALUES
                 (:slug, :full_name, :specialty_id, :photo_id, :short_bio, :nmc_registration_number, :status, :availability_status, :sort_order, :is_featured)'
            );
        } else {
            $stmt = $pdo->prepare(
                'UPDATE doctors
                 SET slug = :slug,
                     full_name = :full_name,
                     specialty_id = :specialty_id,
                     photo_id = :photo_id,
                     short_bio = :short_bio,
                     nmc_registration_number = :nmc_registration_number,
                     status = :status,
                     availability_status = :availability_status,
                     sort_order = :sort_order,
                     is_featured = :is_featured
                 WHERE id = :id'
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }

        $stmt->bindValue(':slug', $doctor['slug']);
        $stmt->bindValue(':full_name', $doctor['full_name']);
        if ($doctor['specialty_id'] === '') {
            $stmt->bindValue(':specialty_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':specialty_id', (int) $doctor['specialty_id'], PDO::PARAM_INT);
        }
        if ($doctor['photo_id'] === '') {
            $stmt->bindValue(':photo_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':photo_id', (int) $doctor['photo_id'], PDO::PARAM_INT);
        }
        $stmt->bindValue(':short_bio', $doctor['short_bio'] !== '' ? $doctor['short_bio'] : null);
        $stmt->bindValue(':nmc_registration_number', $doctor['nmc_registration_number'] !== '' ? $doctor['nmc_registration_number'] : null);
        $stmt->bindValue(':status', $doctor['status']);
        $stmt->bindValue(':availability_status', $doctor['availability_status']);
        $stmt->bindValue(':sort_order', (int) $doctor['sort_order'], PDO::PARAM_INT);
        $stmt->bindValue(':is_featured', (int) $doctor['is_featured'], PDO::PARAM_INT);
        $stmt->execute();

        $doctorId = $id ?? (int) $pdo->lastInsertId();

        $pdo->prepare('DELETE FROM doctor_qualifications WHERE doctor_id = :id')->execute([':id' => $doctorId]);
        $insertQualification = $pdo->prepare(
            'INSERT INTO doctor_qualifications (doctor_id, qualification, institution, qualification_year, sort_order)
             VALUES (:doctor_id, :qualification, :institution, :qualification_year, :sort_order)'
        );
        foreach ($qualifications as $index => $qualification) {
            $insertQualification->execute([
                ':doctor_id' => $doctorId,
                ':qualification' => $qualification['qualification'],
                ':institution' => $qualification['institution'] !== '' ? $qualification['institution'] : null,
                ':qualification_year' => $qualification['qualification_year'] !== '' ? (int) $qualification['qualification_year'] : null,
                ':sort_order' => $index,
            ]);
        }

        $pdo->prepare('DELETE FROM doctor_schedules WHERE doctor_id = :id')->execute([':id' => $doctorId]);
        $insertSchedule = $pdo->prepare(
            'INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, location_label, appointment_required, is_visible, sort_order)
             VALUES (:doctor_id, :day_of_week, :start_time, :end_time, :location_label, :appointment_required, :is_visible, :sort_order)'
        );
        foreach ($schedules as $index => $schedule) {
            $insertSchedule->execute([
                ':doctor_id' => $doctorId,
                ':day_of_week' => (int) $schedule['day_of_week'],
                ':start_time' => $schedule['start_time'] !== '' ? $schedule['start_time'] : null,
                ':end_time' => $schedule['end_time'] !== '' ? $schedule['end_time'] : null,
                ':location_label' => $schedule['location_label'] !== '' ? $schedule['location_label'] : null,
                ':appointment_required' => $schedule['appointment_required'],
                ':is_visible' => $schedule['is_visible'],
                ':sort_order' => $index,
            ]);
        }

        $pdo->commit();
        return $doctorId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function doctor_render_form(array $doctor, array $qualifications, array $schedules, array $specialtyOptions, array $mediaOptions, string $action, string $mode, array $errors = []): void
{
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    if ($qualifications === []) {
        $qualifications = [['qualification' => '', 'institution' => '', 'qualification_year' => '']];
    }
    if ($schedules === []) {
        $schedules = [['day_of_week' => '1', 'start_time' => '', 'end_time' => '', 'location_label' => '', 'appointment_required' => 1, 'is_visible' => 1]];
    }
    ?>
  <?php if ($errors !== []): ?>
    <div class="toast error" role="alert"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="grid settings-grid">
      <div class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">person</span>Basic Information</h2>
          <div class="form-grid">
            <div class="field">
              <label for="full_name">Full Name</label>
              <input class="input" id="full_name" name="full_name" type="text" maxlength="150" value="<?= e((string) $doctor['full_name']) ?>" required>
            </div>
            <div class="field">
              <label for="slug">URL Slug</label>
              <input class="input" id="slug" name="slug" type="text" maxlength="191" value="<?= e((string) $doctor['slug']) ?>" required>
              <p class="hint">Lowercase letters, numbers, and hyphens only.</p>
            </div>
            <div class="field">
              <label for="specialty_id">Specialty</label>
              <select class="select" id="specialty_id" name="specialty_id">
                <option value="">No specialty selected</option>
                <?php foreach ($specialtyOptions as $specialty): ?>
                  <option value="<?= e((string) $specialty['id']) ?>" <?= (string) $doctor['specialty_id'] === (string) $specialty['id'] ? 'selected' : '' ?>>
                    <?= e((string) $specialty['title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="nmc_registration_number">NMC Registration Number</label>
              <input class="input" id="nmc_registration_number" name="nmc_registration_number" type="text" maxlength="100" value="<?= e((string) $doctor['nmc_registration_number']) ?>">
            </div>
            <div class="field full">
              <label for="short_bio">Short Bio</label>
              <textarea class="textarea" id="short_bio" name="short_bio" rows="5"><?= e((string) $doctor['short_bio']) ?></textarea>
            </div>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">school</span>Qualifications</h2>
          <div class="grid" id="qualifications">
            <?php foreach ($qualifications as $index => $qualification): ?>
              <div class="form-grid qualification-row">
                <div class="field">
                  <label for="qualification_<?= $index ?>">Qualification</label>
                  <input class="input" id="qualification_<?= $index ?>" name="qualification[<?= $index ?>]" type="text" maxlength="255" value="<?= e((string) $qualification['qualification']) ?>">
                </div>
                <div class="field">
                  <label for="institution_<?= $index ?>">Institution</label>
                  <input class="input" id="institution_<?= $index ?>" name="institution[<?= $index ?>]" type="text" maxlength="255" value="<?= e((string) $qualification['institution']) ?>">
                </div>
                <div class="field">
                  <label for="qualification_year_<?= $index ?>">Year</label>
                  <input class="input" id="qualification_year_<?= $index ?>" name="qualification_year[<?= $index ?>]" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>" value="<?= e((string) $qualification['qualification_year']) ?>">
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="secondary-btn" type="button" id="addQualification">Add Qualification</button>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">calendar_month</span>Availability Schedule</h2>
          <div class="grid" id="schedules">
            <?php foreach ($schedules as $index => $schedule): ?>
              <div class="form-grid schedule-row">
                <div class="field">
                  <label for="schedule_day_<?= $index ?>">Day</label>
                  <select class="select" id="schedule_day_<?= $index ?>" name="schedule_day[<?= $index ?>]">
                    <?php foreach ($days as $dayNumber => $dayName): ?>
                      <option value="<?= $dayNumber ?>" <?= (string) $schedule['day_of_week'] === (string) $dayNumber ? 'selected' : '' ?>><?= e($dayName) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="field">
                  <label for="schedule_start_<?= $index ?>">Start Time</label>
                  <input class="input" id="schedule_start_<?= $index ?>" name="schedule_start[<?= $index ?>]" type="time" value="<?= e(substr((string) $schedule['start_time'], 0, 5)) ?>">
                </div>
                <div class="field">
                  <label for="schedule_end_<?= $index ?>">End Time</label>
                  <input class="input" id="schedule_end_<?= $index ?>" name="schedule_end[<?= $index ?>]" type="time" value="<?= e(substr((string) $schedule['end_time'], 0, 5)) ?>">
                </div>
                <div class="field">
                  <label for="schedule_location_<?= $index ?>">Location</label>
                  <input class="input" id="schedule_location_<?= $index ?>" name="schedule_location[<?= $index ?>]" type="text" maxlength="150" value="<?= e((string) $schedule['location_label']) ?>">
                </div>
                <div class="field">
                  <label>Options</label>
                  <label class="hint"><input class="checkbox" type="checkbox" name="schedule_appointment_required[<?= $index ?>]" value="1" <?= (int) $schedule['appointment_required'] === 1 ? 'checked' : '' ?>> Appointment required</label>
                  <label class="hint"><input class="checkbox" type="checkbox" name="schedule_visible[<?= $index ?>]" value="1" <?= (int) $schedule['is_visible'] === 1 ? 'checked' : '' ?>> Show schedule</label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="secondary-btn" type="button" id="addSchedule">Add Schedule</button>
        </section>
      </div>

      <aside class="grid">
        <section class="card form-card">
          <h2><span class="material-symbols-outlined">image</span>Photo</h2>
          <div class="field">
            <label for="photo_id">Doctor Photo</label>
            <select class="select" id="photo_id" name="photo_id">
              <option value="">No photo selected</option>
              <?php foreach ($mediaOptions as $media): ?>
                <option value="<?= e((string) $media['id']) ?>" <?= (string) $doctor['photo_id'] === (string) $media['id'] ? 'selected' : '' ?>>
                  <?= e((string) ($media['alt_text'] ?: $media['file_path'])) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="hint">Photos come from the media library. If none exist yet, leave this empty.</p>
          </div>
        </section>

        <section class="card form-card">
          <h2><span class="material-symbols-outlined">settings_suggest</span>Publication Settings</h2>
          <div class="form-grid">
            <div class="field">
              <label for="status">Publishing Status</label>
              <select class="select" id="status" name="status">
                <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                  <option value="<?= e($value) ?>" <?= $doctor['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="availability_status">Availability</label>
              <select class="select" id="availability_status" name="availability_status">
                <?php foreach (['hidden' => 'Hidden', 'accepting' => 'Accepting Appointments', 'unavailable' => 'Unavailable'] as $value => $label): ?>
                  <option value="<?= e($value) ?>" <?= $doctor['availability_status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label for="sort_order">Sort Order</label>
              <input class="input" id="sort_order" name="sort_order" type="number" min="0" step="1" value="<?= e((string) $doctor['sort_order']) ?>">
            </div>
            <div class="field">
              <label>Featured Provider</label>
              <label class="hint"><input class="checkbox" type="checkbox" name="is_featured" value="1" <?= (int) $doctor['is_featured'] === 1 ? 'checked' : '' ?>> Promote in featured doctor areas</label>
            </div>
          </div>
        </section>
      </aside>
    </div>

    <div class="sticky-actions">
      <span class="hint"><?= $mode === 'create' ? 'New doctor profile will be saved with the selected status.' : 'Changes are saved only when you press Save Doctor.' ?></span>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="secondary-btn" href="doctors.php">Cancel</a>
        <button class="primary-btn" type="submit">Save Doctor</button>
      </div>
    </div>
  </form>

  <script>
  (function () {
    var fullName = document.getElementById('full_name');
    var slug = document.getElementById('slug');
    var qualifications = document.getElementById('qualifications');
    var addQualification = document.getElementById('addQualification');
    var schedules = document.getElementById('schedules');
    var addSchedule = document.getElementById('addSchedule');

    if (fullName && slug) {
      fullName.addEventListener('blur', function () {
        if (slug.value.trim() !== '') return;
        slug.value = fullName.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
      });
    }

    if (qualifications && addQualification) {
      addQualification.addEventListener('click', function () {
        var index = qualifications.querySelectorAll('.qualification-row').length;
        var row = document.createElement('div');
        row.className = 'form-grid qualification-row';
        row.innerHTML =
          '<div class="field"><label for="qualification_' + index + '">Qualification</label><input class="input" id="qualification_' + index + '" name="qualification[' + index + ']" type="text" maxlength="255"></div>' +
          '<div class="field"><label for="institution_' + index + '">Institution</label><input class="input" id="institution_' + index + '" name="institution[' + index + ']" type="text" maxlength="255"></div>' +
          '<div class="field"><label for="qualification_year_' + index + '">Year</label><input class="input" id="qualification_year_' + index + '" name="qualification_year[' + index + ']" type="number" min="1900" max="<?= (int) date('Y') + 1 ?>"></div>';
        qualifications.appendChild(row);
      });
    }

    if (schedules && addSchedule) {
      addSchedule.addEventListener('click', function () {
        var index = schedules.querySelectorAll('.schedule-row').length;
        var options = <?php echo json_encode($days, JSON_THROW_ON_ERROR); ?>;
        var dayOptions = options.map(function (label, value) {
          return '<option value="' + value + '">' + label + '</option>';
        }).join('');
        var row = document.createElement('div');
        row.className = 'form-grid schedule-row';
        row.innerHTML =
          '<div class="field"><label for="schedule_day_' + index + '">Day</label><select class="select" id="schedule_day_' + index + '" name="schedule_day[' + index + ']">' + dayOptions + '</select></div>' +
          '<div class="field"><label for="schedule_start_' + index + '">Start Time</label><input class="input" id="schedule_start_' + index + '" name="schedule_start[' + index + ']" type="time"></div>' +
          '<div class="field"><label for="schedule_end_' + index + '">End Time</label><input class="input" id="schedule_end_' + index + '" name="schedule_end[' + index + ']" type="time"></div>' +
          '<div class="field"><label for="schedule_location_' + index + '">Location</label><input class="input" id="schedule_location_' + index + '" name="schedule_location[' + index + ']" type="text" maxlength="150"></div>' +
          '<div class="field"><label>Options</label><label class="hint"><input class="checkbox" type="checkbox" name="schedule_appointment_required[' + index + ']" value="1" checked> Appointment required</label><label class="hint"><input class="checkbox" type="checkbox" name="schedule_visible[' + index + ']" value="1" checked> Show schedule</label></div>';
        schedules.appendChild(row);
      });
    }
  })();
  </script>
    <?php
}
