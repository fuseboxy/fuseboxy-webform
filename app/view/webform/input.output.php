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
*/
?><div 
	id="<?php echo $fieldID; ?>"
	<?php if ( !empty($fieldConfig['class']) ) : ?>class="<?php echo $fieldConfig['class']; ?>"<?php endif; ?>
	<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
><?php echo $fieldValue; ?></div>