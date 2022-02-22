<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<structure name="$fieldConfig">
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
			<number name="$optIndex" />
			<string name="$optValue" />
			<string name="$optText" />
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
$radioID = $fieldID.'-'.$optIndex;
?><div class="form-check"><?php
	// field
	if ( !empty($editable) ) :
		$isChecked = ( $fieldValue == $optValue );
		?><input
			type="radio"
			id="<?php echo $radioID; ?>"
			class="form-check-input"
			<?php if ( empty($fieldConfig['readonly']) ) : ?>
				name="<?php echo $dataFieldName; ?>"
			<?php else : ?>
				disabled
			<?php endif; ?>
			value="<?php echo htmlspecialchars($optValue); ?>"
			<?php if ( $isChecked ) echo 'checked'; ?>
			<?php if ( !empty($fieldConfig['required']) and $optIndex == 0 ) echo 'required'; ?>
		 /><label 
			for="<?php echo $radioID; ?>" 
			class="form-check-label <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		><?php echo $optText; ?></label><?php
		// attribute [readonly] does not work on input[type=radio]
		// ===> disable input[type=radio] and submit value through hidden field
		if ( !empty($fieldConfig['readonly']) and $isChecked ) :
			?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="<?php echo $fieldValue; ?>" /><?php
		endif;
	// readonly
	elseif ( $fieldValue == $optValue ) :
		?><i class="form-check-input fa fa-check text-primary"></i> <strong class="text-primary"><?php echo $optText; ?></strong><?php
	else :
		?><i class="form-check-input far fa-circle text-muted"></i> <span><?php echo $optText; ?></span><?php
	endif;
?></div>