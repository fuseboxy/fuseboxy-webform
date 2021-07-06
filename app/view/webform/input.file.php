<?php /*
<fusedoc>
	<description>
		render file (ajax) upload field
	</description>
	<io>
		<in>
			<boolean name="$editable" />
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
				<number name="filesize" comments="server-side uses byte for validation; client-side uses KB for validation" />
				<list name="filetype" delim="," />
				<string name="filesizeError" comments="error message shown when file size failed" />
				<string name="filetypeError" comments="error message shown when file type failed" />
				<string name="buttonText" comments="button text when no file chosen" />
				<string name="buttonAltText" comments="button text when has file chosen" />
			</structure>
		</in>
		<out>
			<string name="uploaderID" scope="url" oncondition="xfa.uploaderHandler" />
			<string name="fieldName" scope="url" oncondition="xfa.uploaderHandler" />
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
$btnText = empty($fieldValue) ? $fieldConfig['buttonText'] : $fieldConfig['buttonAltText'];
?><div class="webform-input-file"><?php
	// field
	if ( !empty($editable) ) :
		?><label for="<?php echo $fieldID; ?>" class="form-control-file btn btn-light text-left p-3 position-relative"><?php
			if ( empty($fieldConfig['readonly']) ) :
				// psuedo-hidden field to submit
				// ===> to be updated after ajax upload
				?><input 
					type="text" 
					class="w-0 p-0 op-0 position-absolute"
					name="data[<?php echo $fieldName; ?>]"
					value="<?php echo htmlspecialchars($fieldValue); ?>" 
					style="bottom: 0;"
					<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
				/><?php
				// remove button
				?><button 
					type="button"
					aria-label="Remove"
					class="btn-remove close float-right"
					<?php if ( empty($fieldValue) ) : ?>style="display: none;"<?php endif; ?>
				>&times;</button><?php
				// upload button
				?><button 
					type="button" 
					id="<?php echo $fieldID; ?>" 
					class="btn-upload btn btn-sm btn-primary mr-2"
					data-upload-handler="<?php echo F::url($xfa['uploadHandler'].'&uploaderID='.$fieldID.'&fieldName='.$fieldName); ?>"
					data-upload-progress="<?php echo F::url($xfa['uploadProgress']); ?>"
					data-filesize="<?php echo Webform::fileSizeInBytes($fieldConfig['filesize']); ?>"
					data-filetype="<?php echo $fieldConfig['filetype'];  ?>"
					data-filetype-error="<?php echo $fieldConfig['filetypeError']; ?>"
					data-filesize-error="<?php echo $fieldConfig['filesizeError']; ?>"
					data-button-text="<?php echo $fieldConfig['buttonText']; ?>"
					data-button-alt-text="<?php echo $fieldConfig['buttonAltText']; ?>"
				><?php echo $btnText; ?></button><?php
			endif;
			// preview link
			if ( !empty($fieldValue) ) :
				?><a href="<?php echo $fieldValue; ?>" class="preview-link small" target="_blank"><?php
					if ( in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['gif','jpg','jpeg','png']) ) :
						?><img src="<?php echo $fieldValue; ?>" class="img-thumbnail mt-2" alt="" /><?php
					else :
						echo basename($fieldValue);
					endif;
				?></a><?php
			endif;
		?></label><?php
	// readonly
	else :
		?><div class="bg-light rounded p-3"><?php
			// file link or image
			if ( !empty($fieldValue) ) :
				?><a href="<?php echo $fieldValue; ?>" class="small" target="_blank"><?php
					if ( in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['gif','jpg','jpeg','png']) ) :
						?><img src="<?php echo htmlspecialchars($fieldValue); ?>" class="img-thumbnail" alt="" /><?php
					else :
						?><strong><?php echo basename($fieldValue); ?></strong><?php
					endif;
				?></a><?php
			// empty (placeholder to maintain container height)
			else :
				?><span>&nbsp;</span><?php
			endif;
		?></div><?php
	endif;
?></div>