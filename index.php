<?php
$error_message = '';
// CODDED BY - DARK LMNx9 (t.me/x_LMNx9)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_password = trim($_POST['password'] ?? '');
    $password_entries = file('passlist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $valid = false;
    foreach ($password_entries as $entry) {
        $data = json_decode($entry, true);
        if (isset($data['password']) && $data['password'] === $input_password) {
            $valid = true;
            header('Location: panel.php?password=' . urlencode($input_password));
            exit;
        }
    }
    if (!$valid) {
        $error_message = 'আপনি ভুল পাসওয়ার্ড দিয়েছেন । দয়া করে সঠিক পাসওয়ার্ড টি লিখুন, সঠিক পাসওয়ার্ড এর জন্য 👉 GET ACCESS KEY 👈 এখানে ক্লিক করুন...!';
    }
}
?>
<?php include('lmnXindex.html'); ?>
<?php if ($error_message): include('lmnXerror.html'); endif; ?>
