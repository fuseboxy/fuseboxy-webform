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
$hasLabel = !empty($fieldConfig['label']);
$hasInlineLabel = !empty($fieldConfig['inline-label']);
$hasRequiredMark = !empty($fieldConfig['required']);

// normal label
if ( $hasLabel or ( !$hasInlineLabel and $hasRequiredMark ) ) :
	?><label for="<?php echo $fieldID; ?>"><?php
		// label text
		if ( $hasLabel ) echo $fieldConfig['label'];
		// required mark
		if ( !$hasInlineLabel and $hasRequiredMark ) :
			?><span class="text-danger ml-1">*</span><?php
		endif;
	?></label><?php
endif;