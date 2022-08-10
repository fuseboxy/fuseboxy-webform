<?php
// define realtime filter (when necessary)
if ( !isset($fieldConfig['dataAllowed']) ) $fieldConfig['dataAllowed'] = '0123456789.';

// capture original output
ob_start();
include F::appPath('view/webform/input.text.php');
$output = ob_get_clean();

// replace class name
$output = str_ireplace('webform-input-text', 'webform-input-number', $output);

// display
echo $output;