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
*/ ?>
<form 
	id="webform-form"
	method="post"
	class="<?php echo Webform::$config['beanType']; ?>"
	<?php if ( !empty($arguments['step']) ) : ?>data-step="<?php echo $arguments['step']; ?>"<?php endif; ?>
><?php
// display multiple steps, or...
if ( isset($fieldLayoutAll) ) :
	foreach ( array_values($fieldLayoutAll) as $i => $fieldLayout ) :
		if ( $i ) echo '<br /><br />';
		include F::appPath('view/webform/form.body.php');
	endforeach;
// display single step
elseif ( isset($fieldLayout) ) :
	include F::appPath('view/webform/form.body.php');
endif;
// captcha
include F::appPath('view/webform/form.captcha.php');
// button
include F::appPath('view/webform/form.button.php');
?></form>