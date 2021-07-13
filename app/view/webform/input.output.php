<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-output input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// output
	?><div 
		id="<?php echo $fieldID; ?>"
		class="<?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
	><?php echo $fieldValue; ?></div>
</div>