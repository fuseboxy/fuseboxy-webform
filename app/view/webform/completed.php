<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform">
				<structure name="customText">
					<string name="completed" />
				</structure>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="webform-completed"><?php
F::alert([ 'type' => 'success', 'message' => $webform['customText']['completed'] ]);
?></div>