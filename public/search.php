<?php

namespace openSB;

global $betty;

use Orange\OrangeException;
use Orange\Templating;

require_once dirname(__DIR__) . '/private/class/common.php';

require_once dirname(__DIR__) . '/orange/classes/Pages/SubmissionSearch.php';

$query = $_GET['query'] ?? null;
$type = ($_GET['type'] ?? 'recent');
$page_number = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? $_GET['page'] : 1);

try {
    $page = new \Orange\Pages\SubmissionSearch($betty, $type, $page_number, $query);
    $data = $page->getData();
} catch (OrangeException $e) {
    $e->page();
}

$twig = new Templating($betty);

echo $twig->render('browse.twig', [
    'data' => $data,
    'page' => $page_number,
]);