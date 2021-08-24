<?php /*
<fusedoc>
	<description>
		render table which submits array-of-structure
	</description>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<array name="$fieldValue">
				<structure name="+" />
			</arrya>
			<string name="$dataFieldName" />
			<structure name="$fieldConfig">
				<string name="tableTitle" optional="yes" />
				<structure name="tableHeader" optional="yes">
					<string name="~columnHeader~" value="~columnWidth~" />
				</structure>
				<file name="tableRow" optional="yes" example="/path/to/table/row.php" />
				<boolean name="appendRow" />
				<boolean name="removeRow" />
			</structure>
			<structure name="$xfa">
				<string name="appendRow" optional="yes" />
				<string name="removeRow" optional="yes" />
			</structure>
		</in>
		<out>
			<string name="fieldName" scope="url" oncondition="xfa.appendRow" />
			<structure name="data" scope="form" optional="yes">
				<array name="~fieldName~">
					<structure name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $fieldID; ?>" class="webform-input-table">
	<table class="table table-bordered mb-0">
		<thead><?php
			// title
			if ( !empty($fieldConfig['tableTitle']) ) :
				$colspan = count($fieldConfig['tableHeader'] ?? []);
				if ( !empty($xfa['appendRow']) ) $colspan++;
				?><tr class="bg-light">
					<th colspan="<?php echo $colspan; ?>" class="bb-0"><?php echo $fieldConfig['tableTitle']; ?></th>
				</tr><?php
			endif;
			?><tr class="text-center bg-white small"><?php
				// header
				if ( !empty($fieldConfig['tableHeader']) ) :
					foreach ( $fieldConfig['tableHeader'] as $headerText => $headerWidth ) :
						if ( is_numeric($headerText) ) list($headerText, $headerWidth) = [ $headerWidth, '' ];
						?><th <?php if ( !empty($headerWidth) ) echo "width='{$headerWidth}'"; ?>><?php echo $headerText; ?></th><?php
					endforeach;
				endif;
				// button
				if ( !empty($fieldConfig['appendRow']) and !empty($xfa['appendRow']) ) :
					?><th width="50" class="text-center px-0"><?php
						?><a 
							href="<?php echo F::url($xfa['appendRow'].'&fieldName='.$fieldName); ?>"
							class="btn btn-sm btn-success"
							data-toggle="ajax-load"
							data-target="#<?php echo $fieldID; ?> > fieldset"
							data-mode="append"
							data-loading="none"
						><i class="fa fa-fw fa-plus small"></i></a><?php
					?></th><?php
				endif;
			?></tr>
		</thead>
	</table>
	<fieldset><?php
		// content
		if ( !empty($fieldValue) ) :
			foreach ( $fieldValue as $rowIndex => $rowItem ) :
				include $fieldConfig['tableRow'];
			endforeach;
		endif;
	?></fieldset>
</div>