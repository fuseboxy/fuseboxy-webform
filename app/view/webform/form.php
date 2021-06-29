<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$config" scope="Webform">
				<string name="beanType" />
			</structure>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
			</structure>
			<class name="Captcha" />
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<form id="webform-form" method="post" class="<?php echo Webform::$config['beanType']; ?>"><?php
include 'form.body.php';
if ( isset($xfa['submit']) and class_exists('Captcha') ) include 'form.captcha.php';
include 'form.button.php';
?></form>