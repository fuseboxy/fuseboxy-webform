<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="submit" optional="yes" />
				<string name="update" optional="yes" />
				<string name="back" optional="yes" />
				<string name="next" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
?><div id="webform-form-button" class="form-group mt-5"><?php
	if ( isset($xfa['submit']) or isset($xfa['update']) ) :
		?><div class="text-center"><?php
			// submit button
			if ( isset($xfa['submit']) ) :
				?><button 
					type="submit"
					class="btn btn-lg btn-primary btn-update mx-1"
					formaction="<?php echo F::url($xfa['submit']); ?>"
				>Submit</button><?php
			endif;
			// update button
			if ( isset($xfa['update']) ) :
				?><button 
					type="submit"
					class="btn btn-lg btn-primary btn-submit mx-1"
					formaction="<?php echo F::url($xfa['update']); ?>"
				>Update</button><?php
			endif;
		?></div><?php
	endif;
	// separator
	if ( ( isset($xfa['submit']) or isset($xfa['update']) ) and ( isset($xfa['back']) or isset($xfa['next']) ) ) :
		?><hr class="mt-5 mb-4" /><?php
	endif;
	if ( isset($xfa['back']) or isset($xfa['next']) ) :
		?><div class="overflow-auto"><?php
			// back button
			if ( isset($xfa['back']) ) :
				?><a 
					class="btn btn-light btn-back float-left"
					href="<?php echo F::url($xfa['back']); ?>"
				><i class="fa fa-arrow-left"></i> Back</a><?php
			endif;
			// next button
			if ( isset($xfa['next']) ) :
				?><button
					type="submit"
					class="btn btn-primary btn-next float-right"
					formaction="<?php echo F::url($xfa['next']); ?>"
				>Next <i class="fa fa-arrow-right ml-1"></i></button><?php
			endif;
		?></div><?php
	endif;
?></div>