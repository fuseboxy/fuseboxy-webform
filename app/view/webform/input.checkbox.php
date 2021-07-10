<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldName" />
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
	if ( !empty($editable) ) :
		?><input type="hidden" name="data[<?php echo $fieldName; ?>]" value="" /><?php
	endif;
	// display
	$optIndex = 0;
	foreach ( $fieldConfig['options'] as $optValue => $optText ) :
		if ( is_array($optText) ) :
			$optGroupLabel = $optValue;
			$optGroupItems = $optText;
			?><small><strong><?php echo $optGroupLabel; ?></strong></small><?php
			foreach ( $optGroupItems as $optValue => $optText ) :
				include F::appPath('view/webform/input.checkbox.item.php');
				$optIndex++;
			endforeach;
		else :
			include F::appPath('view/webform/input.checkbox.item.php');
			$optIndex++;
		endif;
	endforeach;
?></div>