<?php
$translator = require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";

echo '<h1>' . $translator->get('welcome') . '</h1>';
echo '<p>Your framework is successfully running1</p>';
echo "<pre>";
require_once "manual-tests/test-select.php";