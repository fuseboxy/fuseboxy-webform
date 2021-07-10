<?php
// capture original output
ob_start();
include F::appPath('view/webform/input.text.php');
$output = ob_get_clean();

// replace class name
$output = str_ireplace('webform-input-text', 'webform-input-email', $output);

// display
echo $output;