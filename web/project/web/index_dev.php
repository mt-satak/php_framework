<?php

require '../bootstrap.php';
require '../MiniBlogApplication.php';

$app = new MiniBlogApplication(true);
try {
    $app->run();
} catch (HttpNotFoundException $e) {
    echo 'HttpNotFoundException!!!';
}