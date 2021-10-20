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
			<string name="$dataFieldName" />
			<array name="$fieldValue">
				<structure name="+" />
			</arrya>
			<structure name="$fieldConfig">
				<string name="tableTitle" optional="yes" />
				<structure name="tableHeader" optional="yes">
					<string name="~columnHeader~" value="~columnWidth~" />
				</structure>
				<structure name="tableRow" optional="yes">
					<structure name="~rowFieldName~" />
				</structure>
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
			<structure name="data" scope="form" optional="yes" oncondition="editable">
				<array name="~fieldName~">
					<structure name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $fieldID; ?>" class="webform-input-table">
	<header><?php include F::appPath('view/webform/input.table.header.php'); ?></header>
	<fieldset><?php
		// empty hidden field (when necessary)
		// ===> avoid nothing submitted when no row
		// ===> for the scenario which user remove all rows and submit the change
		if ( !empty($editable) ) :
			?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="" /><?php
		endif;
		// table content
		if ( !empty($fieldValue) ) :
			foreach ( $fieldValue as $rowIndex => $rowItem ) :
				include F::appPath('view/webform/input.table.row.php');
			endforeach;
		endif;
	?></fieldset>
</div>