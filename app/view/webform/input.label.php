<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$isEditMode" optional="yes" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<mixed name="$fieldValue" />
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
$toggleIcon     =  "<i id='toggle-{$fieldID}' class='far fa-plus-square transition-none mr-2'></i>";
$isRequired     = !empty($fieldConfig['required']);
$hasLabel       = !empty($fieldConfig['label']);
$hasInlineLabel = !empty($fieldConfig['inline-label']);


// table-header-style label (when necessary)
if ( $fieldConfig['format'] == 'table' and $hasLabel ) :
	?><table 
		class="table table-bordered small mb-0 <?php if ( empty($fieldValue) and !empty($isEditMode) ) echo 'cursor-pointer'; ?>"
		<?php if ( empty($fieldValue) and !empty($isEditMode) ) : ?>
			data-toggle="collapse"
			data-target="#<?php echo $fieldID; ?>"
			onclick="
				var $table = $('#<?php echo $fieldID; ?>');
				var $icon  = $('#toggle-<?php echo $fieldID; ?>');
				if ( !$table.hasClass('show') ) {
					$icon.removeClass('fa-plus-square').addClass('fa-minus-square');
					window.setTimeout(function(){ $table.filter(':not(:has(.webform-input-table-row))').find('.btn-append-row').click(); }, 200);
				} else {
					$icon.removeClass('fa-minus-square').addClass('fa-plus-square');
					$table.find('.btn-remove-row').click();
				}
			"
		<?php endif; ?>
	>
		<thead class="thead-light">
			<tr><th class="bb-0"><?php
				if ( empty($fieldValue) and !empty($isEditMode) ) echo $toggleIcon;
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