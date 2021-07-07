<?php
// capture original output
ob_start();
include 'input.text.php';
$output = ob_get_clean();

// replace class name
$output = str_ireplace('webform-input-text', 'webform-input-email', $output);

// display
echo $output;