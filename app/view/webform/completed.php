<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$result">
				<string name="success" />
				<array name="warning" optional="yes">
					<string name="+" />
				</array>
				<number name="lastInsertID" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="webform-completed"><?php
	if ( !empty($result['success']) ) :
		?><div class="alert alert-success"><?php echo $result['success']; ?></div><?php
	endif;
	if ( !empty($result['warning']) ) :
		?><div class="alert alert-warning"><?php echo implode('<br />', $result['warning']); ?></div><?php
	endif;
?></div>