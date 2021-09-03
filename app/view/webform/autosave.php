<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="autosave" optional="yes" />
			</structure>
			<structure name="$webform">
				<structure name="customMessage">
					<string name="neverSaved" />
					<string name="lastSavedAt" />
					<string name="lastSavedOn" />
				</structure>
				<structure name="customButton">
					<structure name="autosave">
						<string name="text" />
					</structure>
				</structure>
			</structure>
			<datetime name="$lastSaved" optional="yes" />
		</in>
		<out>
			<structure name="data" scope="form" oncondition="xfa.autosave" comments="copy fields to this form dynamically when submit" />
		</out>
	</io>
</fusedoc>
*/
if ( isset($xfa['autosave']) ) :
	?><form 
		id="webform-autosave"
		method="post"
		class="toast mb-md-5 mb-3 mr-3"
		action="<?php echo F::url($xfa['autosave']); ?>"
		data-toggle="ajax-submit"
		data-target="#webform-autosave"
		data-loading="none"
		onsubmit="$('#webform-form [name^=data]:not([disabled])').each(function(){
			$(this).clone().hide().val( $(this).val() ).appendTo('#webform-autosave');
		});"
	>
		<header class="toast-header small text-nowrap"><?php
			if ( empty($lastSaved) ) :
				?><span><?php echo $webform['customMessage']['neverSaved']; ?></span><?php
			else :
				?><time datetime="<?php echo $lastSaved; ?>"><?php
					$isToday = ( date('Ymd') == date('Ymd',  strtotime($lastSaved)) );
					echo $webform['customMessage'][ $isToday ? 'lastSavedAt' : 'lastSavedOn' ];
					echo date($isToday ? 'g:i a' : 'j M Y',  strtotime($lastSaved));
				?></time><?php
			endif;
		?></header>
		<div class="toast-body p-2">
			<button type="submit" class="btn btn-sm btn-block btn-primary rounded text-nowrap">
				<span class="timer d-inline-block ml-n2 mr-1" style="width: 15px;"><input
					type="hidden"
					value="0"
					data-max="60"
					data-width="15"
					data-height="15"
					data-fgColor="white"
					data-bgColor="#007bff"
					data-thickness="1"
					readonly
				/></span>
				<span class="text"><?php echo $webform['customButton']['text']; ?></span>
			</button>
		</div>
	</form><?php
endif;