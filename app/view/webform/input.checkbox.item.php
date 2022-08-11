<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$isEditMode" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<list name="$fieldValue" delim="|">
				<string name="+" />
			</list>
			<structure name="$fieldConfig">
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
				<boolean name="inline" optional="yes" />
			</structure>
			<number name="$optIndex" />
			<string name="$optValue" />
			<string name="$optText" />
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<array name="~fieldName~">
					<string name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/
$checkboxID = $fieldID.'-'.$optIndex;
$fieldValue = is_array($fieldValue) ? $fieldValue : array_filter(explode('|', $fieldValue));
?><div class="form-check <?php if ( !empty($fieldConfig['inline']) ) echo 'form-check-inline'; ?>"><?php
	// field
	if ( !empty($isEditMode) ) :
		$isChecked = in_array($optValue, $fieldValue);
		?><input
			type="checkbox"
			id="<?php echo $checkboxID; ?>"
			class="form-check-input cursor-pointer"
			value="<?php echo htmlspecialchars($optValue); ?>"
			<?php if ( empty($fieldConfig['readonly']) ) : ?>
				name="<?php echo $dataFieldName; ?>[]"
			<?php else : ?>
				disabled
			<?php endif; ?>
			<?php if ( $isChecked ) echo 'checked'; ?>
			<?php if ( !empty($fieldConfig['required']) and $optIndex == 0 ) echo 'required'; ?>
		/><label 
			for="<?php echo $checkboxID; ?>" 
			class="form-check-label cursor-pointer <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		><?php echo $optText; ?></label><?php
		// attribute [readonly] does not work on input[type=checkbox]
		// ===> disable input[type=checkbox] and submit value through hidden field
		if ( !empty($fieldConfig['readonly']) and $isChecked ) :
			?><input type="hidden" name="<?php echo $dataFieldName; ?>[]" value="<?php echo $optValue; ?>" /><?php
		endif;
	// readonly
	elseif ( in_array($optValue, $fieldValue) ) :
		?><i class="form-check-input fa fa-check text-primary"></i> <strong class="text-primary"><?php echo $optText; ?></strong><?php
	else :
		?><i class="form-check-input far fa-square text-muted"></i> <span><?php echo $optText; ?></span><?php
	endif;
?></div>