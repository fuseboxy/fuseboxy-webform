<?php /*
<fusedoc>
	<description>
		display row of dynamic table
	</description>
	<io>
		<in>
			<string name="$rowIndex" optional="yes" />
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<array name="$fieldValue">
				<structure name="+" />
			</arrya>
			<structure name="$fieldConfig">
				<structure name="tableHeader" optional="yes">
					<string name="~headerText~" value="~columnWidth~" />
				</structure>
				<structure name="tableRow">
					<structure name="~rowFieldName~" />
				</structure>
				<boolean name="removeRow" />
			</structure>
			<structure name="$xfa">
				<string name="removeRow" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes" oncondition="editable">
				<array name="~fieldName~">
					<structure name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/
$rowIndex = $rowIndex ?? Util::uuid();
$rowID = 'row-'.$rowIndex;
?><div id="<?php echo $rowID; ?>" class="webform-input-table-row">
	<table class="table table-bordered small mb-0">
		<tbody class="bg-white">
			<tr><?php
				// display each field
				$rowColumnIndex = 0;
				foreach ( $fieldConfig['tableRow'] as $rowColumnKey => $rowColumnItems ) :
					// obtain column width from [tableHeader] config
					if ( empty($fieldConfig['tableHeader']) ) $columnWidth = '';
					else $columnWidth = array_values($fieldConfig['tableHeader'])[$rowColumnIndex] ?? '';
					// multiple fields in same column
					// ===> apply directly
					if ( is_numeric($rowColumnKey) and is_array($rowColumnItems) ) :
						$rowFieldInSameColumn = $rowColumnItems;
					// only field name specified
					// ===> put into container & assign empty config
					elseif ( is_numeric($rowColumnKey) and is_string($rowColumnItems) ) :
						$rowFieldInSameColumn = array([ $rowColumnItems => array() ]);
					// field name & config specified
					// ===> put into container
					else :
						$rowFieldInSameColumn = array([$rowColumnKey => $rowColumnItems]);
					endif;
					// display column
					?><td <?php if ( !empty($columnWidth) ) echo "width='{$columnWidth}'"; ?>><?php
						// go through each field in same column
						foreach ( $rowFieldInSameColumn as $rowFieldName => $rowFieldConfig ) :
							// render with function
							// ===> avoid modifying certain important variables
							// ===> (e.g. editable, fieldID, fieldName, fieldValue, fieldConfig)
							if ( !function_exists('webform__inputTableRow__renderField') ) :
								function webform__inputTableRow__renderField($fieldName, $fieldConfig, $tableFieldValue, $editable){
									// determine other essential variables
									$fieldID = Webform::fieldName2fieldID($fieldName);
									$fieldValue = Webform::getNestedArrayValue($tableFieldValue, explode('.', $fieldName, 2)[1]);
									$dataFieldName = Webform::fieldName2dataFieldName($fieldName);
									// determine default format (when necessary)
									if     ( empty($fieldConfig['format']) and !empty($fieldConfig['options']) ) $fieldConfig['format'] = 'dropdown';
									elseif ( empty($fieldConfig['format']) or  $fieldConfig['format'] === true ) $fieldConfig['format'] = 'text';
									// reuse input template
									ob_start();
									include F::appPath("view/webform/input.{$fieldConfig['format']}.php");
									// done!
									return ob_get_clean();
								}
							endif;
							// determine actual field name (e.g. workexp.0.employer)
							$actualFieldName = "{$fieldName}.{$rowIndex}.{$rowFieldName}";
							// display row field
							echo webform__inputTableRow__renderField($actualFieldName, $rowFieldConfig, $fieldValue, !empty($editable));
						endforeach;
					?></td><?php
					// continue...
					$rowColumnIndex++;
				endforeach;
				// remove button
				if ( !empty($xfa['removeRow']) and !empty($fieldConfig['removeRow']) and !empty($editable) ) :
					?><td width="50" class="text-center px-0">
						<a 
							href="<?php echo F::url($xfa['removeRow']); ?>"
							class="btn btn-sm btn-square btn-danger mt-1"
							data-toggle="ajax-load"
							data-target="#<?php echo $rowID; ?>"
						><i class="fa fa-fw fa-minus small"></i></a>
					</td><?php
				endif;
			?></tr>
		</tbody>
	</table>
</div>