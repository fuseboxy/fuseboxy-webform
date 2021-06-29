<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$config" scope="Webform">
				<string name="beanType" />
			</structure>
			<structure name="$xfa">
				<string name="back" optional="yes" />
				<string name="next" optional="yes" />
				<string name="edit" optional="yes" />
				<string name="submit" optional="yes" />
				<string name="update" optional="yes" />
			</structure>
			<structure name="$fieldLayoutAll" optional="yes" comments="display multiple steps">
				<structure name="~stepName~">
					<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" />
				</structure>
			</structure>
			<structure name="$fieldLayout" optional="yes" comments="display single step">
				<list name="~fieldNameList~" value="~fieldWidthList~" delim="|" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<form id="webform-form" method="post" class="<?php echo Webform::$config['beanType']; ?>"><?php
// display single or multiple steps
if ( isset($fieldLayoutAll) ) foreach ( $fieldLayoutAll as $fieldLayout ) include 'form.body.php';
elseif ( isset($fieldLayout) ) include 'form.body.php';
// captcha
if ( ( isset($xfa['submit']) or isset($xfa['edit']) ) and class_exists('Captcha') ) :
	include 'form.captcha.php';
endif;
// button
if ( isset($xfa['back']) or isset($xfa['next']) or isset($xfa['edit']) or isset($xfa['submit']) or isset($xfa['update']) ) :
	include 'form.button.php';
endif;
?></form>