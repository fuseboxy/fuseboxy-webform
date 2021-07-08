<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform">
				<structure name="customText">
					<string name="closed" />
				</structure>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="webform-closed"><?php
F::alert([ 'type' => 'warning', 'message' => $webform['customText']['closed'] ]);
?></div>