<?php /*
<fusedoc>
	<description>
		https://github.com/brinley/jSignature/
	</description>
	<io>
		<in>
			<boolean name="$editable" />
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
?><div class="webform-input-signature form-control-plaintext text-center bg-light rounded p-2"><?php
	// field
	if ( !empty($editable) ) :
		// psuedo-hidden field to submit
		// ===> to be updated after ajax upload
		if ( empty($fieldConfig['readonly']) ) :
			?><input 
				type="text" 
				class="w-0 p-0 op-0 position-absolute"
				name="data[<?php echo $fieldName; ?>]"
				value="<?php echo htmlspecialchars($fieldValue); ?>" 
				style="bottom: 0;"
				<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
			/><?php
		endif;
		// clear button
		?><button 
			type="button"
			class="btn-clear close position-absolute mr-2"
			style="right: 0; <?php if ( empty($fieldValue) ) echo 'display: none;'; ?>"
		>&times;</button><?php
		// signature
		?><div 
			class="signature-pad"
			<?php if ( !empty($fieldValue) ) : ?>style="display: none;"<?php endif; ?>
		></div><?php
	endif;
	// display signature (not upload yet)
	if ( !empty($fieldValue) and substr($fieldValue, -6) == '</svg>' ) :
		?><div class="signature-image"><?php echo $fieldValue; ?></div><?php
	// display signature (uploaded)
	elseif ( !empty($fieldValue) ) :
		?><img class="signature-image" src="<?php echo htmlspecialchars($fieldValue); ?>" alt="" /><?php
	endif;
?></div>