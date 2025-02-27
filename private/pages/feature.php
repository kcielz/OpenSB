<?php

namespace OpenSB;

global $database, $auth;

use SquareBracket\UnorganizedFunctions;

$id = ($_GET['v'] ?? null);

if (!$auth->isUserLoggedIn())
{
    UnorganizedFunctions::Notification("Please login to continue.", "/login.php");
}

$submission = new \SquareBracket\SubmissionData($database, $id);

if (!$id) {
    UnorganizedFunctions::Notification("You have not specified the submission.", "/");
}

if ($auth->getUserBanData() || $submission->getTakedown()) {
    UnorganizedFunctions::Notification("You cannot proceed with this action.", "/");
}

$data = $submission->getData();

if (!$data) {
    UnorganizedFunctions::Notification("This submission does not exist.", "/");
}

if (!$auth->getUserID() == $data["author"]) {
    UnorganizedFunctions::Notification("This is not your submission.", "/");
}

if ($database->query("UPDATE users SET featured_submission = ? WHERE id = ?",
    [$data["id"], $auth->getUserID()])) {
    UnorganizedFunctions::Notification("You have successfully changed your profile's featured submission.", "/user?name=" . $auth->getUserData()["name"], "success");
}