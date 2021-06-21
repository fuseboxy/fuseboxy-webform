<?php /*
<fusedoc>
	<description>
		render file (ajax) upload field
	</description>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<number name="filesize" optional="yes" />
				<list name="filetype" delim="," optional="yes" />
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
$btnText = empty($fieldValue) ? 'Choose File' : 'Choose Another File';
?><label
	for="<?php echo $fieldID; ?>"
	class="form-control-file btn btn-light text-left p-3"
	<?php if ( !empty($attr['filesize']) ) : ?>data-file-size="<?php echo $attr['filesize']; ?>"<?php endif; ?>
	<?php if ( !empty($attr['filetype']) ) : ?>data-file-type="<?php echo $attr['filetype'];  ?>"<?php endif; ?>
	data-field="<?php echo $fieldName; ?>"
><?php
	// upload button
	?><button 
		type="button" 
		id="<?php echo $fieldID; ?>" 
		class="btn-ajax-upload btn btn-sm btn-primary mr-2"
	><?php echo $btnText; ?></button><?php
	// preview link
	?><small class="preview"><?php if ( !empty($fieldValue) ) echo Webform::fileLink($fieldValue); ?></small><?php
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