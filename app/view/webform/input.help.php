<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldConfig">
				<string name="help" optional="yes" comments="help text show under input field" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
if ( !empty($fieldConfig['help']) ) :
	?><small class="form-text text-muted"><?php echo $fieldConfig['help']; ?></small><?php
endif;