/**
 * @name FrozenPlugin Administrative JavaScript Utilities.
 * @author PxO Ink
 * @authorURI https://github.com/mookman288/FrozenPlugin
 * @copyright 2015 PxO Ink. Some rights reserved. 
 * @license MIT
 */

//Upon document load.
jQuery(document).ready(function() {
	//Avoid bubbling on click of slide toggle targeters. 
	jQuery('.wrap').on('click', '*[data-slide]', function(e) {
		//Prevent default.
		e.preventDefault();
		
		//Toggle the element being targeted. 
		jQuery(jQuery(this).data('target')).slideToggle();
		
		//Return false.
		return false;
	});
	
	//If the media editor has been included. 
	if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
		//Avoid bubbling on click of the media uploader.
		jQuery('.wrap').on('click', '*[data-media-upload]', function(e) {
			//Prevent default.
			e.preventDefault();
			
			//Get the target.
			var	$target	=	jQuery(jQuery(this).data('target'));

			//Override the media attachment function to populate input.
			wp.media.editor.send.attachment	=	
				function(properties, attachment) {
					//Populate the input with the attachment data.
					$target.val(attachment.url);
				};

			//Open the modal bound to a unique ID. 
			wp.media.editor.open($target.attr('id'));
			
			//Return false.
			return false;
		});
	}
	
	//For each color picker. 
	jQuery('*[data-color-picker]').each(function() {
		//Declare variables.
		var	$this	=	jQuery(this);
		var	$target	=	jQuery($this.data('target'));
		
		//Initiate Farbtastic color picker.
		$this.farbtastic(function(color) {
			//Change the value.
			$target.val(color);
			
			//Set the background color of the parent. 
			$target.parent().css('background-color', color);
		});
	});
});