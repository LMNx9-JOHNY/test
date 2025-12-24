<?php
$input_password = trim($_GET['password'] ?? '');
$password_entries = file('passlist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$valid = false;

foreach ($password_entries as $entry) {
    $data = json_decode($entry, true);
    if (isset($data['password']) && $data['password'] === $input_password) {
        $valid = true;
        break;
    }
}

if (!$valid) {
    header('Location: index.php');
    exit;
}
// CODDED BY - DARK LMNx9 (t.me/x_LMNx9)
?>
<?php include('lmnXpanel.html'); ?>
