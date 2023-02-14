<?php /*
<fusedoc>
	<description>
		output [data-toggle-xxx] attribute into input element
		===> handle single/double-quotes in json string cautiously
	</description>
	<io>
		<in>
			<string name="$fieldID" example="webform-field-first_name" />
			<string name="$fieldName" example="first_name" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" />
			<structure name="$fieldConfig">
				<structure name="toggleAttr" comments="toggle attribute of another field while modifying this field" />
				<structure name="toggleValue" comments="toggle value of another field while modifying this field" />
				<structure name="toggleClass" comments="toggle class of another field while modifying this field" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
