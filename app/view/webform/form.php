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
			<string name="$step" />
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<form id="webform-form" method="post" class="<?php echo Webform::$config['beanType']; ?>" data-step="<?php echo $step; ?>"><?php
// display single or multiple steps
if ( isset($fieldLayoutAll) ) foreach ( $fieldLayoutAll as $fieldLayout ) include F::appPath('view/webform/form.body.php');
elseif ( isset($fieldLayout) ) include F::appPath('view/webform/form.body.php');
// captcha
include F::appPath('view/webform/form.captcha.php');
// button
include F::appPath('view/webform/form.button.php');
?></form>