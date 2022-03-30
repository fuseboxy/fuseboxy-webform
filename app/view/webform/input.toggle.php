<?php /*
<fusedoc>
	<description>
		write [data-toggle-xxx] attribute into element by javascript
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
				<structure name="toggleAttr" comments="toggle attribute of another field while modifying this field">
					<string_or_array name="target" comments="field name; use array for multiple fields" />
					<structure name="setAttr">
						<structure name="when|whenNot">
							<structure name="~thisFieldValue~">
								<string_or_boolean name="~targetFieldAttrName~" value="~targetFieldAttrValue~" comments="use string to set attribute value; use {true} to add attribute without value; use {false|null} to remove attribute" />
							</structure>
						</structure>
					</structure>
				</structure>
				<structure name="toggleValue" comments="toggle value of another field while modifying this field">
					<array name="target" comments="field name; use array for multiple fields" />
					<structure name="setValue">
						<structure name="when|whenNot">
							<string name="~thisFieldValue~" value="~targetFieldValue~" />
						</structure>
					</structure>
				</structure>
				<structure name="toggleClass" comments="toggle class of another field while modifying this field">
					<array name="target" comments="field name; use array for multiple fields" />
					<structure name="addClass|removeClass">
						<structure name="when|whenNot">
							<string name="~thisFieldValue~" value="~className~" />
						</structure>
					</structure>
				</structure>
				<structure name="toggleWrapperClass" comments="toggle class of [webform-input] wrapper of another field while modifying this field">
					<array name="target" comments="field name; use array for multiple fields" />
					<structure name="addClass|removeClass">
						<structure name="when|whenNot">
							<string name="~thisFieldValue~" value="~className~" />
						</structure>
					</structure>
				</structure>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// define all toggle types
$all = array(
	'data-toggle-attr' => 'toggleAttr',
	'data-toggle-value' => 'toggleValue',
	'data-toggle-class' => 'toggleClass',
	'data-toggle-wrapper-class' => 'toggleWrapperClass',
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
			// append [targetSelector] to the settings
			$fieldConfig[$toggleType]['targetSelector'] = implode(',', array_map(function($item){
				return '.webform-input [name=\"'.Webform::fieldName2dataFieldName($item).'\"]';
			}, $fieldConfig[$toggleType]['target']));
			// write data attribute to element
			?>$('.webform-input [name="<?php echo $dataFieldName; ?>"]').attr('<?php echo $attrName; ?>', '<?php echo json_encode($fieldConfig[$toggleType]); ?>');<?php
		endif;
	endforeach;
	?> });</script><?php
endif;