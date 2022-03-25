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
	foreach ( $fieldConfig['options'] ?? [] as $optValue => $optText ) :
		// option group
		if ( is_array($optText) ) :
			$optGroupLabel = $optValue;
			$optGroupItems = $optText;
			// group label
			if ( !empty($optGroupItems) ) :
				?><small><strong><?php echo $optGroupLabel; ?></strong></small><?php
			endif;
			// option list
			foreach ( $optGroupItems as $optValue => $optText ) :
				if ( $optText !== false and $optText !== null ) :
					include F::appPath('view/webform/input.radio.item.php');
					$optIndex++;
				endif;
			endforeach;
		// individual option
		elseif ( $optText !== false and $optText !== null ) :
			include F::appPath('view/webform/input.radio.item.php');
			$optIndex++;
		endif;
	endforeach;
?></div>