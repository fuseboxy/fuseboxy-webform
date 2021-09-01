<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform">
				<string name="beanType" />
			</structure>
			<string name="$formStep" optional="yes" />
			<string name="$formBody" comments="form fields" />
		</in>
		<out />
	</io>
</fusedoc>
*/
?><form 
	id="webform-form"
	method="post"
	class="<?php echo $webform['beanType']; ?>"
	data-step="<?php echo $formStep ?? ''; ?>"
><?php
// fields
echo $formBody;
// captcha
include F::appPath('view/webform/form.captcha.php');
// button
include F::appPath('view/webform/form.button.php');
?></form>