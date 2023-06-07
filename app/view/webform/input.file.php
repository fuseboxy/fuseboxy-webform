<?php /*
<fusedoc>
	<description>
		render file (ajax) upload field
	</description>
	<io>
		<in>
			<boolean name="$isEditMode" />
			<structure name="$xfa">
				<string name="ajaxUpload" optional="yes" />
				<string name="removeFile" optional="yes" />
			</structure>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
			</structure>
			<structure name="$webform">
				<structure name="customButton">
					<structure name="chooseFile|chooseAnother">
						<string name="text" />
					</structure>
				</structure>
			</structure>
		</in>
		<out>
			<string name="uploaderID" scope="url" oncondition="xfa.uploaderHandler" />
			<string name="fieldName" scope="url" oncondition="xfa.uploaderHandler" comments="access webform-fieldConfig for server validation" />
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
?><div id="<?php echo $fieldID; ?>" class="webform-input-file"><?php
	// field
	if ( !empty($isEditMode) ) :
		?><label for="choose-<?php echo $fieldID; ?>" class="form-control-file btn btn-light text-left mb-0 p-3 position-relative"><?php
			// when field-readonly
			// ===> [hidden] field to pass value
			if ( !empty($fieldConfig['readonly']) ) :
				?><input 
					type="hidden" 
					name="<?php echo $dataFieldName; ?>"
					value="<?php echo htmlspecialchars($fieldValue); ?>" 
				/><?php
			// when not field-readonly
			// ===> [upload] button to choose file
			// ===> [psuedo-hidden] field to submit value
			else :
				// psuedo-hidden field
				// ===> instead of using hidden field
				// ===> for browser validation message
				?><input 
					type="text"
					class="w-0 p-0 op-0 position-absolute"
					name="<?php echo $dataFieldName; ?>"
					value="<?php echo htmlspecialchars($fieldValue); ?>"
					style="bottom: 0;"
					<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
					<?php include F::appPath('view/webform/input.data_toggle.php'); ?>
					<?php if ( isset($xfa['ajaxUpload']) ) : ?>
						data-toggle="ajax-upload"
						data-target="#<?php echo $fieldID; ?>"
						data-form-action="<?php echo F::url($xfa['ajaxUpload']); ?>"
						data-choose-button="#choose-<?php echo $fieldID; ?>"
						data-preview="#preview-<?php echo $fieldID; ?>"
					<?php endif; ?>
				/><?php
				// remove button
				if ( isset($xfa['removeFile']) ) :
					?><a 
						href="<?php echo F::url($xfa['removeFile']); ?>"
						class="btn-remove close float-right"
						aria-label="Remove"
						data-toggle="ajax-load"
						data-target="#<?php echo $fieldID; ?>"
						data-transition="none"
						data-callback="$('#<?php echo $fieldID; ?>-ajax-upload').remove();"
					>&times;</a><?php
				endif;
				// upload button
				if ( isset($xfa['ajaxUpload']) ) :
					?><button 
						type="button" 
						id="choose-<?php echo $fieldID; ?>" 
						class="btn-choose btn btn-sm btn-primary mr-2"
						data-button-text="<?php echo $webform['customButton']['chooseFile']['text']; ?>"
						data-button-alt-text="<?php echo $webform['customButton']['chooseAnother']['text']; ?>"
					><?php echo $webform['customButton'][ empty($fieldValue) ? 'chooseFile' : 'chooseAnother' ]['text']; ?></button><?php
				endif;
			endif;
			// preview link
			if ( !empty($fieldValue) ) :
				?><a 
					href="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>"
					id="preview-<?php echo $fieldID; ?>"
					class="preview-link small"
					target="_blank"
				><?php
					if ( in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['gif','jpg','jpeg','png']) ) :
						?><img src="<?php echo $fieldValue; ?>" class="img-thumbnail d-block mt-2" alt="" /><?php
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
				?><a href="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>" class="small" target="_blank"><?php
					if ( in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['gif','jpg','jpeg','png']) ) :
						?><img src="<?php echo dirname($fieldValue).'/'.urlencode(basename($fieldValue)); ?>" class="img-thumbnail" alt="" /><?php
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