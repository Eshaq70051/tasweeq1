<?php
header('Content-Type: text/plain');
echo "PHP IS WORKING! Version: " . phpversion() . "\n";
echo "Loaded extensions:\n" . implode(", ", get_loaded_extensions());
