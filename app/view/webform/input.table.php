<?php /*
<fusedoc>
	<description>
		render table which submits array-of-structure
		===> pre-collapsed table when empty value
		===> click table title to expand
	</description>
	<io>
		<in>
			<boolean name="$isEditMode" />
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
				<structure name="scriptPath">
					<file name="tableHeader" />
					<file name="tableRow" />
				</structure>
			</structure>
			<structure name="$xfa">
				<string name="appendRow" optional="yes" />
				<string name="removeRow" optional="yes" />
			</structure>
		</in>
		<out>
			<string name="fieldName" scope="url" oncondition="xfa.appendRow" />
			<structure name="data" scope="form" optional="yes" oncondition="isEditMode">
				<array name="~fieldName~">
					<structure name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo $fieldID; ?>" class="webform-input-table table-responsive <?php if ( !empty($fieldConfig['label']) and empty($fieldValue) ) echo 'collapse'; ?>">
	<header><?php include $fieldConfig['scriptPath']['tableHeader']; ?></header>
	<fieldset><?php
		// empty hidden field (when necessary)
		// ===> avoid nothing submitted when no row
		// ===> for the scenario which user remove all rows and submit the change
		if ( !empty($isEditMode) ) :
			?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="" /><?php
		endif;
		// table content
		if ( !empty($fieldValue) ) :
			foreach ( $fieldValue as $rowIndex => $rowItem ) include $fieldConfig['scriptPath']['tableRow'];
		endif;
	?></fieldset>
</div>