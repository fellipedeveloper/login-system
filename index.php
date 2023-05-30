<?php

use Source\Models\Admin;

require __DIR__ . "/vendor/autoload.php";

$admin = (new Admin())->findById(2);
$admin->email = "fellipe@gmail.com";
$admin->save();
var_dump($admin);