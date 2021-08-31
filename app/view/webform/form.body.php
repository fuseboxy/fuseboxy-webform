<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldLayout">
				<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" comments="use bootstrap grid layout for width">
					<list name="~fieldNameSubList~" delim="," comments="multiple fields in same column" />
				</list>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
foreach ( $fieldLayout as $key => $val ) :
	$output = Webform::renderStepRow($key, $val);
	if ( $output === false ) F::alert([ 'type' => 'warning', 'message' => Webform::error() ]);
	else echo $output;
endforeach;