<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$config" scope="Webform">
				<string name="beanType" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<form id="webform-form" class="<?php echo Webform::$config['beanType']; ?>" method="post"><?php
include 'form.body.php';
include 'form.button.php';
?></form>