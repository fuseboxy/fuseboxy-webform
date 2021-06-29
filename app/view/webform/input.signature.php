<?php /*
<fusedoc>
	<description>
		https://github.com/brinley/jSignature/
	</description>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<string name="buttonText" comments="button text when no file chosen" />
				<string name="buttonAltText" comments="button text when has file chosen" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
$btnText = '';
?><div class="webform-input-signature form-control-plaintext bg-light rounded p-2" style="height: 200px;"><?php
	// field
	if ( Webform::mode() != 'view' ) :
		// psuedo-hidden field to submit
		// ===> to be updated after ajax upload
		if ( empty($fieldConfig['readonly']) ) :
			?><input 
				type="text" 
				class="w-0 p-0 op-0 position-absolute"
				name="data[<?php echo $fieldName; ?>]"
				value="<?php echo $fieldValue; ?>" 
				style="bottom: 0;"
				<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
			/><?php
		endif;
		// signature
		?><div class="signature-pad" style="cursor: pointer;"></div><?php
	// readonly
	else :
		?><img src="<?php echo $fieldValue; ?>" class="d-block mx-auto" alt="" /><?php
	endif;
?></div>