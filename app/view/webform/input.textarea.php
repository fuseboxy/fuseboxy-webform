<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-textarea input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// field
	if ( !empty($editable) ) :
		?><textarea
			id="<?php echo $fieldID; ?>"
			name="<?php echo $dataFieldName; ?>"
			class="form-control <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
		><?php echo htmlspecialchars($fieldValue); ?></textarea><?php
	// readonly
	elseif ( $fieldValue !== '' ) :
		?><div class="form-control-plaintext text-primary"><strong><?php echo nl2br($fieldValue); ?></strong></div><?php
	// empty
	else :
		?><div class="form-control-plaintext text-muted">- - -</div><?php
	endif;
?></div>