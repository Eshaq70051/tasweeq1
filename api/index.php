<?php

// Prevent Laravel from thinking the application is in an /api subfolder
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Forward Vercel requests to Laravel's public entry point
require __DIR__ . '/../public/index.php';
