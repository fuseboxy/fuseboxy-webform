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

// render row field
if ( !function_exists('renderRowField') ) :
	function renderRowField($fieldID, $fieldName, $fieldValue, $fieldConfig){
		global $editable;
		$dataFieldName = Webform::fieldName2dataFieldName($fieldName);
		if ( empty($fieldConfig['format']) or $fieldConfig['format'] === true ) $fieldConfig['format'] = 'text';
		include F::appPath("view/webform/input.{$fieldConfig['format']}.php");
	}
endif;
// display row
?><div id="<?php echo $rowID; ?>" class="webform-input-table-row">
	<table class="table table-bordered mb-0">
		<tbody>
			<tr class="text-center bg-white small"><?php
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
						$rowFieldInSameColumn = array($rowColumnItems => []);
					// field name & config specified
					// ===> put into container
					else :
						$rowFieldInSameColumn = array($rowColumnKey => $rowColumnItems);
					endif;
					// display column
					?><td <?php if ( !empty($columnWidth) ) echo "width='{$columnWidth}'"; ?>><?php
						// go through each field in same column
						foreach ( $rowFieldInSameColumn as $rowFieldName => $rowFieldConfig ) :
							echo "<div>{$rowFieldName}</div>";
							/*echo renderRowField(
								"{$fieldID}-{$rowIndex}-{$rowFieldName}",
								"{$fieldName}.{$rowIndex}.{$rowFieldName}",
								'', //Webform::getNestedArrayValue($fieldValue, "{$rowIndex}.{$rowFieldName}"),
								$rowFieldConfig
							);*/
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