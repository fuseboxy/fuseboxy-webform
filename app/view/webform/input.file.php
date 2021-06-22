<?php /*
<fusedoc>
	<description>
		render file (ajax) upload field
	</description>
	<io>
		<in>
			<structure name="$xfa">
				<string name="uploadHandler" />
				<string name="uploadProgress" />
			</structure>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<number name="filesize" />
				<list name="filetype" delim="," />
				<string name="filesizeError" comments="error message shown when file size failed" />
				<string name="filetypeError" comments="error message shown when file type failed" />
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
$btnText = empty($fieldValue) ? $fieldConfig['buttonText'] : $fieldConfig['buttonAltText'];
?><label for="<?php echo $fieldID; ?>" class="form-control-file btn btn-light text-left p-3"><?php
	// upload button
	?><button 
		type="button" 
		id="<?php echo $fieldID; ?>" 
		class="btn-webform-upload btn btn-sm btn-primary mr-2"
		data-field="<?php echo $fieldName; ?>"
		data-upload-handler="<?php echo F::url($xfa['uploadHandler']); ?>"
		data-upload-progress="<?php echo F::url($xfa['uploadProgress']); ?>"
		data-filesize="<?php echo $fieldConfig['filesize']; ?>"
		data-filetype="<?php echo $fieldConfig['filetype'];  ?>"
		data-filetype-error="<?php echo $fieldConfig['filetypeError']; ?>"
		data-filesize-error="<?php echo $fieldConfig['filesizeError']; ?>"
		data-button-text="<?php echo $fieldConfig['buttonText']; ?>"
		data-button-alt-text="<?php echo $fieldConfig['buttonAltText']; ?>"
	><?php echo $btnText; ?></button><?php
	// preview link
	?><small class="preview ml-2"><?php
	if ( !empty($fieldValue) ) :
		?><a href="<?php echo $fieldValue; ?>" target="_blank"><?php echo basename($fieldValue); ?></a><?php
	endif;
	?></small><?php
	// psuedo-hidden field to submit
	// ===> to be updated after ajax upload
	if ( empty($fieldConfig['readonly']) ) :
		?><input 
			type="text" 
			class="w-0 op-0"
			name="data[<?php echo $fieldName; ?>]"
			value="<?php echo $fieldValue; ?>" 
			<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
		/><?php
	endif;
?></label>