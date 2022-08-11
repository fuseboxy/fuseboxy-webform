<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$isEditMode" />
			<string name="$fieldName" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<structure name="$fieldConfig">
				<structure name="options">
					<structure name="~optGroup~" optional="yes">
						<string name="~optValue~" value="~optText~" />
					</structure>
					<string name="~optValue~" value="~optText~" optional="yes" />
				</structure>
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" value="~empty~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-checkbox"><?php
	// empty hidden field (when necessary)
	// ===> avoid nothing submitted when no checkbox selected
	// ===> for the scenario which user deselect all checkboxes and submit the change
	if ( !empty($isEditMode) ) :
		?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="" /><?php
	endif;
	// display
	$optIndex = 0;
	foreach ( $fieldConfig['options'] ?? [] as $optValue => $optText ) :
		// option group
		if ( is_array($optText) ) :
			$optGroupLabel = $optValue;
			$optGroupItems = $optText;
			?><div class="chkgroup <?php if ( $optIndex ) echo 'mt-2'; ?>"><?php
				// group label
				if ( !empty($optGroupItems) ) :
					?><strong><?php echo $optGroupLabel; ?></strong><?php
				endif;
				// option list
				foreach ( $optGroupItems as $optValue => $optText ) :
					if ( $optText !== false and $optText !== null ) :
						include F::appPath('view/webform/input.checkbox.item.php');
						$optIndex++;
					endif;
				endforeach;
			?></div><?php
		// individual option
		elseif ( $optText !== false and $optText !== null ) :
			include F::appPath('view/webform/input.checkbox.item.php');
			$optIndex++;
		endif;
	endforeach;
?></div>