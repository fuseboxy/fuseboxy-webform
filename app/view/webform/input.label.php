<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<structure name="$fieldConfig">
				<string name="format" />
				<string name="label" optional="yes" />
				<boolean name="required" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// useful variables
$reqMark        = '<span class="text-danger ml-1">*</span>';
$isTable        = ( $fieldConfig['format'] == 'table' );
$isRequired     = !empty($fieldConfig['required']);
$hasLabel       = !empty($fieldConfig['label']);
$hasInlineLabel = !empty($fieldConfig['inline-label']);


// table-header-style label (when necessary)
if ( $isTable and $hasLabel ) :
	?><table class="table table-bordered small mb-0">
		<thead class="thead-light">
			<tr><th class="bb-0"><?php
				if ( $hasLabel ) echo $fieldConfig['label'];
				if ( $isRequired ) echo $reqMark;
			?></th></tr>
		</thead>
	</table><?php


// normal-style label (when necessary)
elseif ( $hasLabel or ( $isRequired and !$hasInlineLabel ) ) :
	?><label for="<?php echo $fieldID; ?>"><?php
		if ( $hasLabel ) echo $fieldConfig['label'];
		if ( !$hasInlineLabel and $isRequired ) echo $reqMark;
	?></label><?php


endif;