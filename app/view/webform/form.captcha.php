<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="update" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
if ( ( isset($xfa['submit']) or isset($xfa['update']) ) and class_exists('Captcha') ) :
	?><div id="webform-form-captcha" class="text-center mt-5"><?php
		echo Captcha::field();
	?></div><?php
endif;
