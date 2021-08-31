<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$config" scope="Webform">
				<string name="beanType" />
			</structure>
			<structure name="$fieldLayoutAll" optional="yes" comments="display multiple steps">
				<structure name="~stepName~">
					<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" />
				</structure>
			</structure>
			<structure name="$fieldLayout" optional="yes" comments="display single step">
				<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" />
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
	class="<?php echo Webform::$config['beanType']; ?>"
	data-step="<?php echo $arguments['step'] ?? ''; ?>"
><?php
// display multiple steps, or...
if ( isset($fieldLayoutAll) ) :
	foreach ( array_values($fieldLayoutAll) as $i => $fieldLayout ) :
		if ( $i ) echo '<br /><br />';
		foreach ( $fieldLayout as $key => $val ) echo Webform::renderStepRow($key, $val);
	endforeach;
// display single step
elseif ( isset($fieldLayout) ) :
	foreach ( $fieldLayout as $key => $val ) echo Webform::renderStepRow($key, $val);
endif;
// captcha
include F::appPath('view/webform/form.captcha.php');
// button
include F::appPath('view/webform/form.button.php');
?></form>