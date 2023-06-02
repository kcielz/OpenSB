<?php

namespace openSB;

global $betty, $bettyTemplate;

use \Betty\Templating;

require_once dirname(__DIR__) . '/private/class/common.php';
require_once dirname(__DIR__) . '/betty/class/pages/FooterOptions.php';

if (isset($_POST['action'])) {
    $optionsArray = [
        'updated' => time(),
        'skin' => $_POST['skin'] ?? 'finalium',
    ];

    setcookie('SBOPTIONS', base64_encode(json_encode($optionsArray)), 2147483647);

    //if (!$error) {
    redirect("index.php?updated=true");
    //}
}

$twig = new Templating($betty, $bettyTemplate);

$skins = [];
foreach($twig->getAllSkins() as $skin) {
    $skins[] = $twig->getSkinMetadata($skin);
}

echo $twig->render('footer_options.twig', [
    'skins' => $skins,
]);