<?php /*
<fusedoc>
	<description>
		render table which submits array-of-structure
	</description>
	<io>
		<in>
			<string name="$fieldName" />
			<structure name="$fieldConfig">
				<string name="tableTitle" optional="yes" />
				<structure name="tableHeader" optional="yes">
					<string name="~columnHeader~" value="~columnWidth~" />
				</structure>
				<structure name="tableRow" optional="yes">
					<structure name="~rowFieldName~" />
				</structure>
				<file name="tableRow" optional="yes" example="/path/to/table/row.php" />
				<boolean name="appendRow" />
			</structure>
			<structure name="$xfa">
				<string name="appendRow" optional="yes" />
			</structure>
		</in>
		<out>
			<string name="fieldName" scope="url" oncondition="xfa.appendRow" />
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-table-header">
	<table class="table table-bordered mb-0">
		<thead><?php
			// check whether to show button
			$showAppendButton = ( !empty($xfa['appendRow']) and !empty($fieldConfig['appendRow']) and !empty($editable) );
			// table title
			if ( !empty($fieldConfig['tableTitle']) ) :
				$columnCount = count($fieldConfig['tableHeader'] ?? []);
				if ( $showAppendButton ) $columnCount++;
				?><tr class="bg-light">
					<th colspan="<?php echo $columnCount; ?>" class="bb-0"><?php echo $fieldConfig['tableTitle']; ?></th>
				</tr><?php
			endif;
			// table header
			?><tr class="text-center bg-white small"><?php
				// column name
				if ( !empty($fieldConfig['tableHeader']) ) :
					foreach ( $fieldConfig['tableHeader'] as $headerText => $columnWidth ) :
						if ( is_numeric($headerText) ) list($headerText, $columnWidth) = [ $columnWidth, '' ];
						?><th <?php if ( !empty($columnWidth) ) echo "width='{$columnWidth}'"; ?>><?php echo $headerText; ?></th><?php
					endforeach;
				endif;
				// append button
				if ( $showAppendButton ) :
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
</div>