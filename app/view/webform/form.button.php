<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="back" optional="yes" />
				<string name="next" optional="yes" />
				<string name="edit" optional="yes" />
				<string name="print" optional="yes" />
				<string name="submit" optional="yes" />
				<string name="update" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
$hasUpperButton = ( !empty($xfa['submit']) or !empty($xfa['update']) or !empty($xfa['edit']) or !empty($xfa['print']) );
$hasLowerButton = ( !empty($xfa['back']) or !empty($xfa['next']) );
if ( $hasUpperButton or $hasLowerButton ) :
	?><div id="webform-form-button" class="form-group mt-5"><?php
		// upper-button
		if ( $hasUpperButton ) :
			?><div class="text-center"><?php
				// submit button
				if ( !empty($xfa['submit']) ) :
					?><button 
						type="submit"
						class="btn btn-lg btn-primary btn-update mx-2"
						formaction="<?php echo F::url($xfa['submit']); ?>"
					><i class="fa fa-paper-plane"></i> Submit</button><?php
				endif;
				// update button
				if ( !empty($xfa['update']) ) :
					?><button 
						type="submit"
						class="btn btn-lg btn-primary btn-submit mx-2"
						formaction="<?php echo F::url($xfa['update']); ?>"
					><i class="fa fa-download"></i> Update</button><?php
				endif;
				// edit button
				if ( !empty($xfa['edit']) ) :
					?><a 
						class="btn btn-lg btn-dark btn-edit mx-2"
						href="<?php echo F::url($xfa['edit']); ?>"
					><i class="fa fa-edit"></i> Edit</a><?php
				endif;
				// print button
				if ( !empty($xfa['print']) ) :
					?><a 
						class="btn btn-lg btn-light btn-print border mx-2"
						href="<?php echo F::url($xfa['print']); ?>"
						target="_blank"
					><i class="fa fa-print"></i> Print</a><?php
				endif;
			?></div><?php
		endif;
		// separator
		if (  $hasUpperButton and $hasLowerButton ) :
			?><hr class="mt-5 mb-4" /><?php
		endif;
		// lower-button
		if ( $hasLowerButton ) :
			?><div class="overflow-auto"><?php
				// back button
				if ( !empty($xfa['back']) ) :
					?><a 
						class="btn btn-light btn-back float-left"
						href="<?php echo F::url($xfa['back']); ?>"
					><i class="fa fa-arrow-left"></i> Back</a><?php
				endif;
				// next button
				if ( !empty($xfa['next']) ) :
					?><button
						type="submit"
						class="btn btn-primary btn-next float-right"
						formaction="<?php echo F::url($xfa['next']); ?>"
					>Next <i class="fa fa-arrow-right ml-1"></i></button><?php
				endif;
			?></div><?php
		endif;
	?></div><?php
endif;
