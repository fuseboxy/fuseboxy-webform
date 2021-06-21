<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="back" optional="yes" />
				<string name="next" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
?><div id="webform-form-button" class="form-group mt-5"><?php
	// submit button
	if ( isset($xfa['submit']) ) :
		?><div class="text-center"><?php
			if ( isset($xfa['submit']) ) :
				?><button type="submit" formaction="<?php echo F::url($xfa['submit']); ?>" class="btn btn-lg btn-primary mx-1">Submit</button><?php
			endif;
		?></div><?php
	endif;
	// separator
	if ( isset($xfa['submit']) and ( isset($xfa['back']) or isset($xfa['next']) ) ) :
		?><hr class="mt-5 mb-4" /><?php
	endif;
	// back & next
	if ( isset($xfa['back']) or isset($xfa['next']) ) :
		?><div class="overflow-auto"><?php
			if ( isset($xfa['back']) ) :
				?><a href="<?php echo F::url($xfa['back']); ?>" class="btn btn-light b-1 float-left"><i class="fa fa-arrow-left"></i> Back</a><?php
			endif;
			if ( isset($xfa['next']) ) :
				?><button type="submit" formaction="<?php echo F::url($xfa['next']); ?>" class="btn btn-primary float-right">Next <i class="fa fa-arrow-right ml-1"></i></button><?php
			endif;
		?></div><?php
	endif;
?></div>