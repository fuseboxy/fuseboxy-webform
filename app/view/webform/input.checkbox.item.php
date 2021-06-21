<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<list name="$fieldValue" delim="|">
				<string name="+" />
			</list>
			<structure name="$fieldConfig">
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
			<number name="$optIndex" />
			<string name="$optValue" />
			<string name="$optText" />
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<array name="~fieldName~">
					<string name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/
$checkboxID = $fieldID.'-'.$optIndex;
$fieldValue = is_array($fieldValue) ? $fieldValue : array_filter(explode('|', $fieldValue));
?><div class="form-check">
	<input
		type="checkbox"
		id="<?php echo $checkboxID; ?>"
		class="form-check-input"
		name="data[<?php echo $fieldName; ?>][]"
		value="<?php echo htmlspecialchars($optValue); ?>"
		<?php if ( in_array($optValue, $fieldValue) ) echo 'checked'; ?>
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($fieldConfig['required']) and $optIndex == 0 ) echo 'required'; ?>
	/><label 
		for="<?php echo $checkboxID; ?>" 
		class="form-check-label <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
	><?php echo $optText; ?></label>
</div>