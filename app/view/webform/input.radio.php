<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldConfig">
				<structure name="options">
					<structure name="~optGroup~" optional="yes">
						<string name="~optValue~" value="~optText~" />
					</structure>
					<string name="~optValue~" value="~optText~" optional="yes" />
				</structure>
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-radio"><?php
	$optIndex = 0;
	foreach ( $fieldConfig['options'] as $optValue => $optText ) :
		if ( is_array($optText) ) :
			$optGroupLabel = $optValue;
			$optGroupItems = $optText;
			?><small><strong><?php echo $optGroupLabel; ?></strong></small><?php
			foreach ( $optGroupItems as $optValue => $optText ) :
				include F::appPath('view/webform/input.radio.item.php');
				$optIndex++;
			endforeach;
		else :
			include F::appPath('view/webform/input.radio.item.php');
			$optIndex++;
		endif;
	endforeach;
?></div>