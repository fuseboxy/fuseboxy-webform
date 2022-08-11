<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$isEditMode" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
if ( !empty($isEditMode) ) :
	?><input 
		type="hidden"
		id="<?php echo $fieldID; ?>"
		name="<?php echo $dataFieldName; ?>"
		value="<?php echo htmlspecialchars($fieldValue); ?>"
	/><?php
endif;