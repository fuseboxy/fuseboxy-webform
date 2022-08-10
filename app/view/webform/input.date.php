<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
				<number name="maxlength" optional="yes" default="10" />
				<string name="dataAllowed" optional="yes" />
				<string name="dataDisallowed" optional="yes" />
				<string name="dateFormat" optional="yes" example="Y-m-d|Y-m|.." />
				<string name="dateLocale" optional="yes" example="en|en-GB|zh|zh-TW|.." />
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
// define realtime filter (when necessary)
if ( !isset($fieldConfig['dataAllowed']) ) $fieldConfig['dataAllowed'] = '0123456789-';

// display field
?><div class="webform-input-date input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// field
	if ( !empty($editable) ) :
		?><input 
			type="text"
			id="<?php echo $fieldID; ?>"
			name="<?php echo $dataFieldName; ?>"
			value="<?php echo htmlspecialchars($fieldValue); ?>"
			class="form-control datepicker br-0 <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			maxlength="<?php echo $fieldConfig['maxlength'] ?? 10; ?>"
			<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
			<?php if ( !empty($fieldConfig['dataAllowed']) ) : ?>data-allowed="<?php echo $fieldConfig['dataAllowed']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['dataDisallowed']) ) : ?>data-disallowed="<?php echo $fieldConfig['dataDisallowed']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['dateFormat']) ) : ?>data-date-format="<?php echo $fieldConfig['dateFormat']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['dateLocale']) ) : ?>data-date-locale="<?php echo $fieldConfig['dateLocale']; ?>"<?php endif; ?>
		/><?php
		// calendar icon
		?><div class="input-group-append">
			<span class="input-group-text px-2 bl-0 <?php echo !empty($fieldConfig['readonly']) ? 'readonly' : 'bg-white'; ?>">
				<i class="far fa-calendar-alt op-30"></i>
			</span>
		</div><?php
	// readonly
	elseif ( $fieldValue !== '' ) :
		?><div class="form-control-plaintext text-primary"><strong><?php echo $fieldValue; ?></strong></div><?php
	// empty
	else :
		?><div class="form-control-plaintext text-muted">- - -</div><?php
	endif;
?></div>