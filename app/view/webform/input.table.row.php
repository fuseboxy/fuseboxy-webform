<?php /*
<fusedoc>
	<description>
		display row of dynamic table
	</description>
	<io>
		<in>
			<structure name="$xfa">
				<string name="removeRow" optional="yes" />
			</structure>
			<string name="$rowIndex" optional="yes" />
			<string name="$fieldName" />
			<array name="$fieldValue">
				<structure name="+" />
			</arrya>
			<structure name="$fieldConfig">
				<structure name="tableHeader" optional="yes">
					<string name="~headerText~" value="~columnWidth~" />
				</structure>
				<structure name="tableRow">
					<structure name="~tableFieldName~" />
				</structure>
				<boolean name="removeRow" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
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
				$tableColumnIndex = 0;
				foreach ( $fieldConfig['tableRow'] as $tableColumnKey => $tableColumnItems ) :
					// multiple fields in same column
					// ===> apply & clean-up each item
					if ( is_numeric($tableColumnKey) and is_array($tableColumnItems) ) :
						$tableFieldInSameColumn = array();
						foreach ( $tableColumnItems as $key => $val ) :
							if ( is_string($val) ) $tableFieldInSameColumn[$val] = array();
							else $tableFieldInSameColumn[$key] = $val;
						endforeach;
					// only field name specified
					// ===> put into container & assign empty config
					elseif ( is_numeric($tableColumnKey) and is_string($tableColumnItems) ) :
						$tableFieldInSameColumn = array($tableColumnItems => []);
					// field name & config specified
					// ===> put into container
					else :
						$tableFieldInSameColumn = array($tableColumnKey => $tableColumnItems);
					endif;
					// obtain column width from [tableHeader] config
					if ( empty($fieldConfig['tableHeader']) ) $colWidth = '';
					else $colWidth = array_values($fieldConfig['tableHeader'])[$tableColumnIndex] ?? '';
					// display column
					?><td class="px-2 pt-2 pb-0" width="<?php echo $colWidth; ?>"><?php
						// go through each field in same column
						foreach ( $tableFieldInSameColumn as $tableFieldName => $tableFieldConfig ) :
							// determine actual field name (e.g. workexp.0.employer)
							$actualFieldName = "{$fieldName}.{$rowIndex}.{$tableFieldName}";
							$tableFieldValue = Webform::nestedArrayGet(explode('.', $actualFieldName, 2)[1], $fieldValue);
							// display table field
							echo Webform::renderField($actualFieldName, $tableFieldConfig, $tableFieldValue);
						endforeach;
					?></td><?php
					// continue...
					$tableColumnIndex++;
				endforeach;
				// remove button
				if ( !empty($xfa['removeRow']) and !empty($fieldConfig['removeRow']) ) :
					?><td width="50" class="text-center px-0 py-2">
						<a 
							href="<?php echo F::url($xfa['removeRow']); ?>"
							class="btn btn-sm btn-square btn-danger btn-remove-row mt-1"
							data-toggle="ajax-load"
							data-target="#<?php echo $rowID; ?>"
						><i class="fa fa-fw fa-minus small"></i></a>
					</td><?php
				endif;
			?></tr>
		</tbody>
	</table>
</div>