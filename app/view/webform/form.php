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
include F::appPath('view/webform/form.body.php');
include F::appPath('view/webform/form.button.php');
?></form>