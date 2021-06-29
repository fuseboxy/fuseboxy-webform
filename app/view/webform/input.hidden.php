<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
if ( Webform::mode() != 'view' ) :
	?><input 
		type="hidden"
		id="<?php echo $fieldID; ?>"
		name="data[<?php echo $fieldName; ?>]"
		value="<?php echo htmlspecialchars($fieldValue); ?>"
	/><?php
endif;