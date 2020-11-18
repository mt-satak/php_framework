<?php

require '../bootstrap.php';
require '../MiniBlogApplication.php';

$app = new MiniBlogApplication(false);
try {
    $app->run();
} catch (HttpNotFoundException $e) {
    echo 'HttpNotFoundException!!!';
}