<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldLayout">
				<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" comments="use bootstrap grid layout for width">
					<list name="~fieldNameSubList~" delim="," comments="multiple fields in same column" />
				</list>
			</structure>
			<structure name="data" scope="$arguments" comments="form data">
				<mixed name="~fieldName~" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) echo Webform::renderStepRow($fieldNameList);