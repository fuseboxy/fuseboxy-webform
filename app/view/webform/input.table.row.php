<?php /*
<fusedoc>
	<description>
		display row of dynamic table
	</description>
	<io>
		<in>
			<string name="$rowIndex" optional="yes" />
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<structure name="$fieldConfig">
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
			<structure name="data" scope="form">
			</structure>
		</out>
	</io>
</fusedoc>
*/
$rowIndex = $rowIndex ?? Util::uuid();
$rowID = 'row-'.$rowIndex;
?><div id="<?php echo $rowID; ?>" class="webform-input-table-row">
	<table class="table table-bordered mb-0">
		<tbody>
			<tr class="text-center bg-white small"><?php
				// display each field
				foreach ( $fieldConfig['tableRow'] as $rowField ) :
					?><td><?php echo $rowField; ?></td><?php
				endforeach;
				// remove button
				if ( !empty($fieldConfig['removeRow']) and !empty($xfa['removeRow']) ) :
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
