<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<string name="inline-label" optional="yes" />
				<boolean name="required" optional="yes" comments="show asterisk at in-label" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
if ( !empty($fieldConfig['icon']) or !empty($fieldConfig['inline-label']) ) :
	?><div class="input-group-prepend">
		<span class="input-group-text <?php if ( empty($editable) ) echo 'rounded mr-3'; ?>"><?php
			if ( !empty($fieldConfig['icon']) ) :
				?><i class="<?php echo $fieldConfig['icon']; ?>"></i><?php
			endif;
			if ( !empty($fieldConfig['inline-label']) ) :
				?><small><?php echo $fieldConfig['inline-label']; ?></small><?php
				if ( !empty($fieldConfig['required']) ) echo '<span class="text-danger ml-1">*</span>';
			endif;
		?></span>
	</div><?php
endif;