<?php

namespace OpenSB;

global $disableRegistration, $enableInviteKeys, $twig, $database;

use SquareBracket\UnorganizedFunctions;
use SquareBracket\Utilities;

if ($disableRegistration) {
    UnorganizedFunctions::Notification("The ability to register has been disabled.", "/");
}

$ipcheck = file_get_contents("https://api.stopforumspam.org/api?ip=" . Utilities::get_ip_address());

if (str_contains($ipcheck, "<appears>yes</appears>")) {
    UnorganizedFunctions::Notification("This IP address appears to be suspicious.", "/index.php");
}

if (isset($_POST['registersubmit'])) {
    $error = "";

    $username = trim($POST['username'] ?? '');
    $pass = $POST['pass1'] ?? '';
    $pass2 = $POST['pass2'] ?? '';
    $mail = filter_var($POST['email'], FILTER_SANITIZE_EMAIL);
    if ($enableInviteKeys) {
        $invite = $POST['invite'];
    }

    if (!isset($username)) $error .= "Blank username. ";
    if (!isset($pass2) || $pass != $pass2) $error .= "The passwords don't match. ";
    //if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $error .= "Invalid email format. ";
    if ($database->result("SELECT COUNT(*) FROM users WHERE name = ?", [$username])) $error .= "Username has already been taken. ";
    if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $username)) $error .= "Username contains invalid characters. "; //the "long-ass arabic character" exploit was fixed a long time ago. -chaziz 6/7/2024
    if ($database->result("SELECT COUNT(*) FROM users WHERE email = ?", [$mail])) $error .= "Email already registered. ";
    if ($database->result("SELECT COUNT(*) FROM users WHERE ip = ?", [Utilities::get_ip_address()]) > 10)
        $error .= "Limit of 10 accounts per IP reached. ";

    if ($enableInviteKeys) {
        $inviteValidationResult = $database->result("SELECT id FROM invite_keys WHERE invite_key = ? AND claimed_by IS NULL", [$invite]);
        if (empty($invite) || !$inviteValidationResult) {
            $error .= "Invalid or missing invite key. ";
        }
    }

    if(!$error) {
        $token = bin2hex(random_bytes(32));
        $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
        $database->query("INSERT INTO users (name, password, token, joined, lastview, title, email, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$username, $hashedPassword, $token, time(), time(), $username, $mail, Utilities::get_ip_address()]);
        $userId = $database->insertId();

        if ($enableInviteKeys) {
            $database->query("UPDATE invite_keys SET claimed_by = ?, claimed_time = ? WHERE invite_key = ?", [$userId, time(), $invite]);
        }

        setcookie('SBTOKEN', $token, 2147483647);

        UnorganizedFunctions::redirect('./');
    } else {
        UnorganizedFunctions::Notification($error, "/register.php");
    }
}

echo $twig->render('register.twig');
