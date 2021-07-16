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
			<structure name="$webform">
				<structure name="config">
					<structure name="customButton">
						<structure name="next|back|edit|submit|update|print">
							<string name="icon" />
							<string name="text" />
						</structure>
					</structure>
				</structure>
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
						class="btn btn-lg btn-primary btn-submit mx-2"
						formaction="<?php echo F::url($xfa['submit']); ?>"
					><?php
						if ( !empty($webform['config']['customButton']['submit']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['submit']['icon']; ?>"></i><?php
						endif;
						if ( !empty($webform['config']['customButton']['submit']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['submit']['text']; ?></span><?php
						endif;
					?></button><?php
				endif;
				// update button
				if ( !empty($xfa['update']) ) :
					?><button 
						type="submit"
						class="btn btn-lg btn-primary btn-update mx-2"
						formaction="<?php echo F::url($xfa['update']); ?>"
					><?php
						if ( !empty($webform['config']['customButton']['update']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['update']['icon']; ?>"></i><?php
						endif;
						if ( !empty($webform['config']['customButton']['update']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['update']['text']; ?></span><?php
						endif;
					?></button><?php
				endif;
				// edit button
				if ( !empty($xfa['edit']) ) :
					?><a 
						class="btn btn-lg btn-dark btn-edit mx-2"
						href="<?php echo F::url($xfa['edit']); ?>"
					><?php
						if ( !empty($webform['config']['customButton']['edit']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['edit']['icon']; ?>"></i><?php
						endif;
						if ( !empty($webform['config']['customButton']['edit']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['edit']['text']; ?></span><?php
						endif;
					?></a><?php
				endif;
				// print button
				if ( !empty($xfa['print']) ) :
					?><a 
						class="btn btn-lg btn-light btn-print border mx-2"
						href="<?php echo F::url($xfa['print']); ?>"
						target="_blank"
					><?php
						if ( !empty($webform['config']['customButton']['print']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['print']['icon']; ?>"></i><?php
						endif;
						if ( !empty($webform['config']['customButton']['print']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['print']['text']; ?></span><?php
						endif;
					?></a><?php
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
					><?php
						if ( !empty($webform['config']['customButton']['back']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['back']['icon']; ?>"></i><?php
						endif;
						if ( !empty($webform['config']['customButton']['back']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['back']['text']; ?></span><?php
						endif;
					?></a><?php
				endif;
				// next button
				if ( !empty($xfa['next']) ) :
					?><button
						type="submit"
						class="btn btn-primary btn-next float-right"
						formaction="<?php echo F::url($xfa['next']); ?>"
					><?php
						if ( !empty($webform['config']['customButton']['next']['text']) ) :
							?><span><?php echo $webform['config']['customButton']['next']['text']; ?></span><?php
						endif;
						if ( !empty($webform['config']['customButton']['next']['icon']) ) :
							?><i class="<?php echo $webform['config']['customButton']['next']['icon']; ?>"></i><?php
						endif;
					?></button><?php
				endif;
			?></div><?php
		endif;
	?></div><?php
endif;
