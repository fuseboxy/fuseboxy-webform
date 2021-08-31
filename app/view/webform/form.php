<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform">
				<string name="beanType" />
			</structure>
			<structure name="$formBody">
				<structure name="~stepName~">
					<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" />
				</structure>
			</structure>
			<string name="step" scope="$arguments" optional="yes" />
		</in>
		<out />
	</io>
</fusedoc>
*/
?><form 
	id="webform-form"
	method="post"
	class="<?php echo $webform['beanType']; ?>"
	data-step="<?php echo implode(array_keys($formBody)); ?>"
><?php
// fields
foreach ( array_values($formBody) as $i => $fieldLayout ) :
	if ( $i ) echo '<br /><br />';
	foreach ( $fieldLayout as $key => $val ) echo Webform::renderStepRow($key, $val);
endforeach;
// captcha
include F::appPath('view/webform/form.captcha.php');
// button
include F::appPath('view/webform/form.button.php');
?></form>