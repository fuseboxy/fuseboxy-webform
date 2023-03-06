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
	<?php if ( (string)$fieldValue == (string)$optValue ) echo 'selected'; ?>
><?php echo htmlspecialchars($optText); ?></option>