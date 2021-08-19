<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldConfigAll">
				<structure name="~fieldName~">
					<string name="format" default="text" comments="output|hidden|text|url|textarea|checkbox|radio|file|image|signature" />
					<string name="label" optional="yes" />
					<string name="placeholder" optional="yes" />
					<!-- options -->
					<structure name="options" optional="yes" comments="show dropdown when specified">
						<string name="~optionValue~" value="~optionText~" optional="yes" />
						<structure name="~optGroup~" optional="yes">
							<structure name="~optionValue~" value="~optionText~" />
						</structure>
					</structure>
					<!-- attribute -->
					<boolean name="required" optional="yes" />
					<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
					<string name="default" optional="yes" comments="filling with this value if field has no value" />
					<string name="value" optional="yes" comments="force filling with this value even if field has value" />
					<!-- styling -->
					<string name="class" optional="yes" />
					<string name="style" optional="yes" />
					<!-- help text -->
					<string name="help" optional="yes" comments="help text show after input field" />
				</structure>
			</structure>
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
foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) :
	// heading & line & output
	if ( Webform::parseStepRow($fieldNameList, true) != 'fields' ) :
		echo Webform::parseStepRow($fieldNameList);
	// field list
	// ===> example : "aaa|bbb|ccc|ddd,eee|x.y.z"
	// ===> result  : ["aaa", "bbb", "ccc", "ddd,eee", "x.y.z"]
	else :
		$fieldNameList = explode('|', $fieldNameList);
		if ( !is_array($fieldWidthList) ) $fieldWidthList = explode('|', $fieldWidthList);
		?><div class="form-row"><?php
			foreach ( $fieldNameList as $i => $fieldNameSubList ) :
				$fieldWidth = !empty($fieldWidthList[$i]) ? "col-{$fieldWidthList[$i]}" : 'col';
				// determine column class
				// ===> example : "foo,bar,ab_cd,x.y.z"
				// ===> result  : "webform-col-foo-bar-ab_cd-x-y-z"
				$colClassName = 'webform-col-'.str_replace([',','.'], '-', $fieldNameSubList);
				// display column
				?><div class="webform-col <?php echo $colClassName; ?> <?php echo $fieldWidth; ?>"><?php
					// when [fieldName] is normal string, e.g. {first_name},
					// ===> form submit the field as {data[first_name]}
					// when [fieldName] is having dot, e.g. {my.nested.var}
					// ===> form submit the fields as {data[my][nested][var]}
					$fieldNameSubList = explode(',', $fieldNameSubList);
					foreach ( $fieldNameSubList as $fieldName ) :
						// check whether empty field
						if ( !empty($fieldName) ) :
							$fieldID = 'webform-input-'.str_replace('.', '-', $fieldName);
							$fieldConfig = $fieldConfigAll[$fieldName];
							$dataFieldName = 'data['.str_replace('.', '][', $fieldName).']';
							// determine value to show in this field
							// ===> precedence: defined-value > submitted-value > default-value > empty
							if ( isset($fieldConfig['value']) ) {
								$fieldValue = $fieldConfig['value'];
							} elseif ( Webform::getNestedArrayValue($arguments['data'], $fieldName) !== null ) {
								$fieldValue = Webform::getNestedArrayValue($arguments['data'], $fieldName);
							} elseif ( isset($fieldConfig['default']) ) {
								$fieldValue = $fieldConfig['default'];
							} else {
								$fieldValue = '';
							}
							// display field
							include F::appPath('view/webform/input.php');
						endif; // if-empty
					endforeach; // foreach-fieldNameSubList
				?></div><!--/.col--><?php
			endforeach; // foreach-fieldNameList
		?></div><!--/.row--><?php
	endif;
endforeach; // foreach-fieldLayout