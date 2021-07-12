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
				<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" comments="use bootstrap grid layout for width" />
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
	// output & heading & line
	if ( Webform::stepRowType($fieldNameList) != 'grid' ) :
		echo Webform::stepRowParse($fieldNameList);
	// grid layout
	else :
		$fieldNameList = explode('|', $fieldNameList);
		$fieldWidthList = is_array($fieldWidthList) ? $fieldWidthList : array_filter(explode('|', $fieldWidthList));
		?><div class="form-row"><?php
			foreach ( $fieldNameList as $i => $fieldName ) :
				$fieldWidth = isset($fieldWidthList[$i]) ? "col-{$fieldWidthList[$i]}" : 'col';
				// display column
				?><div class="<?php echo $fieldWidth; ?>"><?php
					$fieldID = 'webform-input-'.$fieldName;
					$fieldConfig = $fieldConfigAll[$fieldName];
					// defined value > submitted value > default
					if     ( isset($fieldConfig['value'])          ) $fieldValue = $fieldConfig['value'];
					elseif ( isset($arguments['data'][$fieldName]) ) $fieldValue = $arguments['data'][$fieldName];
					elseif ( isset($fieldConfig['default'])        ) $fieldValue = $fieldConfig['default'];
					else $fieldValue = '';
					// display field
					include F::appPath('view/webform/input.php');
				?></div><!--/.col--><?php
			endforeach;
		?></div><!--/.row--><?php
	endif;
endforeach;