<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
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
if ( !empty($editable) ) :
	?><input 
		type="hidden"
		id="<?php echo $fieldID; ?>"
		name="data[<?php echo $fieldName; ?>]"
		value="<?php echo htmlspecialchars($fieldValue); ?>"
	/><?php
endif;