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
foreach ([
	'data-toggle-attr'  => 'toggleAttr',
	'data-toggle-value' => 'toggleValue',
	'data-toggle-class' => 'toggleClass',
] as $attrName => $toggleType ) :
	if ( !empty($fieldConfig[$toggleType]) ) :
		echo $attrName."='".json_encode($fieldConfig[$toggleType], JSON_HEX_APOS)."' ";
	endif;
endforeach;