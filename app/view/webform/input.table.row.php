<?php /*
<fusedoc>
	<description>
		display row of dynamic table
	</description>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<structure name="$fieldConfig">
				<structure name="tableRow">
					<structure name="~rowFieldName~" />
				</structure>
			</structure>
		</in>
		<out>
			<structure name="data" scope="form">
			</structure>
		</out>
	</io>
</fusedoc>
*/
var_dump($fieldConfig['tableRow']);