<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div id="webform-form-captcha" class="text-center mt-5"><?php
	if ( isset($xfa['submit']) and class_exists('Captcha') ) echo Captcha::field();
?></div>