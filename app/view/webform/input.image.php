<?php
// capture original output
ob_start();
include F::appPath('view/webform/input.file.php');
$output = ob_get_clean();

// change class name
$output = str_ireplace('webform-input-file', 'webform-input-image', $output);

// display
echo $output;