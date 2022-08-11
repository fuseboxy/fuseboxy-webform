<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$isEditMode" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
				<number name="maxlength" optional="yes" />
				<number name="minlength" optional="yes" />
				<string name="dataAllowed" optional="yes" />
				<string name="dataDisallowed" optional="yes" />
				<array name="options" optional="yes">
					<string name="+" />
				</array>
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-text input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// field
	if ( !empty($isEditMode) ) :
		?><input 
			type="text"
			id="<?php echo $fieldID; ?>"
			name="<?php echo $dataFieldName; ?>"
			value="<?php echo htmlspecialchars($fieldValue); ?>"
			class="form-control <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($options) ) : ?>list="<?php echo $fieldID; ?>-suggest"<?php endif; ?>
			<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['maxlength']) ) : ?>maxlength="<?php echo $fieldConfig['maxlength']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['minlength']) ) : ?>minlength="<?php echo $fieldConfig['minlength']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
			<?php if ( !empty($fieldConfig['dataAllowed']) ) : ?>data-allowed="<?php echo $fieldConfig['dataAllowed']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['dataDisallowed']) ) : ?>data-disallowed="<?php echo $fieldConfig['dataDisallowed']; ?>"<?php endif; ?>
		/><?php
		// suggestions (when necessary)
		if ( !empty($options) ) :
			?><datalist id="<?php echo $fieldID; ?>-suggest"><?php
				foreach ( $options as $key => $val ) :
					?><option value="<?php echo $val; ?>" /><?php
				endforeach;
			?></datalist><?php
		endif;
	// readonly
	elseif ( $fieldValue !== '' ) :
		?><div class="form-control-plaintext text-primary"><strong><?php echo $fieldValue; ?></strong></div><?php
	// empty
	else :
		?><div class="form-control-plaintext text-muted">- - -</div><?php
	endif;
?></div>