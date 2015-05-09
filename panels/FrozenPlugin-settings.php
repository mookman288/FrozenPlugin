	<div class="wrap">
		<?php $this -> notices -> output(); ?>
		<h2>
			<span class="dashicons dashicons-admin-settings"></span>
			Settings
		</h2>
		<h3>Application Settings</h3>
		<form id="save-FrozenPlugin-options" method="post" 
		action="<?php print($_SERVER['REQUEST_URI']); ?>">
			<!-- Note that WordPress prefers using tables for forms. -->
			<!-- This is generally considered to be bad form. ͼ(-ᴥ•)ͽ -->
			<table class="form-table">
				<colgroup>
					<col />
					<col />
					<col />
				</colgroup>
				<tfoot>
					<tr>
						<td>Update Settings?</td>
						<td>
							<input type="submit" name="submit" class="button button-primary" 
							value="Yes, Update Settings">
						</td>
						<td>
							<?php wp_nonce_field($uniqid, 'nonce'); ?>
							<input type="hidden" name="uniqid" value="<?php print($uniqid); ?>" />
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td>
							<label for="color">Color Picker</label>
						</td>
						<td>
							<input id="color" type="text" size="6" name="options[optional][color]" 
							value="<?php 
								print(esc_attr($this -> options['optional']['color'])); 
							?>" />
						</td>
						<td>
							<span data-color-picker data-target="#color" 
							class="dashicons dashicons-art"></span>
						</td>
					</tr>
					<tr>
						<td>
							<label for="image-upload">
								Image Upload using Media Manager
							</label>
						</td>
						<td>
							<input id="image-upload" type="text" name="options[optional][image]" 
							value="<?php 
								print(esc_attr($this -> options['optional']['image'])); 
							?>" 
							readonly="readonly" class="regular-text" data-media-upload 
							data-target="#image-upload" />
						</td>
						<td>
							<button id="image-upload-button" 
							class="button button-secondary" data-media-upload 
							data-target="#image-upload">
								<span class="dashicons dashicons-admin-media"></span>
								Choose/Upload Media
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>