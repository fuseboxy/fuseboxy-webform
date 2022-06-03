<?php /*
<fusedoc>
	<description>
		write [data-toggle-xxx] attribute into element by javascript (fuseboxy-webform-asset.js)
		===> to avoid too many duplicated display logic in input tags
		===> to avoid handling single/double-quotes in json string
	</description>
	<io>
		<in>
			<string name="$fieldID" example="webform-field-first_name" />
			<string name="$fieldName" example="first_name" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" />
			<structure name="$fieldConfig">
				<structure name="toggleAttr" comments="toggle attribute of another field while modifying this field" />
				<structure name="toggleValue" comments="toggle value of another field while modifying this field" />
				<structure name="toggleClass" comments="toggle class of another field while modifying this field" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// define all toggle types
$all = array(
	'data-toggle-attr'  => 'toggleAttr',
	'data-toggle-value' => 'toggleValue',
	'data-toggle-class' => 'toggleClass',
);

// check any toggle settings
$hasAnyToggle = false;
foreach ( $all as $toggleType ) $hasAnyToggle |= !empty($fieldConfig[$toggleType]);

// display
if ( $hasAnyToggle ) :
	?><script>$(function(){ <?php
	// go through each toggle type
	foreach ( $all as $attrName => $toggleType ) :
		if ( !empty($fieldConfig[$toggleType]) ) :
			// write data attribute to element
			?>$('.webform-input [name="<?php echo $dataFieldName; ?>"]').attr('<?php echo $attrName; ?>', '<?php echo json_encode($fieldConfig[$toggleType]); ?>');<?php
		endif;
	endforeach;
	?> });</script><?php
endif;