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
<div class="input-group"><?php
	// icon
	if ( !empty($fieldConfig['icon']) ) :
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="<?php echo $fieldConfig['icon']; ?>"></i>
			</span>
		</div><?php
	endif;
	// field
	?><textarea
		id="<?php echo $fieldID; ?>"
		name="data[<?php echo $fieldName; ?>]"
		class="form-control form-control-sm <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
	><?php echo htmlspecialchars($fieldValue); ?></textarea><?php
?></div>