<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldValue" />
			<string name="$optValue" />
			<string name="$optText" />
		</in>
		<out />
	</io>
</fusedoc>
*/
?><option 
	value="<?php echo htmlspecialchars($optValue); ?>"
	<?php if ( $fieldValue == $optValue ) echo 'selected'; ?>
><?php echo htmlspecialchars($optText); ?></option>