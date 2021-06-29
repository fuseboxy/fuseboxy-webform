<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
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
<div class="webform-input-textarea input-group"><?php
	// icon
	include 'input.icon.php';
	// field
	if ( Webform::mode() != 'view' ) :
		?><textarea
			id="<?php echo $fieldID; ?>"
			name="data[<?php echo $fieldName; ?>]"
			class="form-control <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
		><?php echo htmlspecialchars($fieldValue); ?></textarea><?php
	// readonly
	else :
		?><div class="form-control text-primary"><strong><?php echo nl2br($fieldValue); ?></strong></div><?php
	endif;
?></div>