<?php 
tsml_assets();

$meeting = tsml_get_meeting();

//define some vars for the map
wp_localize_script('tsml_public', 'tsml_map', array(
	'latitude' => $meeting->latitude,
	'longitude' => $meeting->longitude,
	'location' => get_the_title($meeting->post_parent),
	'address' => $meeting->formatted_address,
	'location_url' => get_permalink($meeting->post_parent),
	'directions_url' => $meeting->directions,
	'directions' => __('Directions', '12-step-meeting-list'),
	'contributions_api_key' => $meeting->contributions_api_key,
));

date_default_timezone_set('America/Los_Angeles'); 
$startDate = tsml_format_next_start($meeting);

get_header();
?>

<div id="tsml">
	<div id="meeting" class="container" itemscope itemtype="http://schema.org/Event">
		<div class="row">
			<div class="col-md-10 col-md-offset-1 main">
			
				<div class="page-header">
					<h1 itemprop="name"><?php echo tsml_format_name($meeting->post_title, $meeting->types)?></h1>
					<?php echo tsml_link(get_post_type_archive_link('tsml_meeting'), '<i class="glyphicon glyphicon-chevron-right"></i> ' . __('Back to Meetings', '12-step-meeting-list'), 'tsml_meeting')?>
				</div>
	
				<div class="row">
					<div class="col-md-4">

						<div class="panel panel-default">
							<a href="<?php echo $meeting->directions?>" class="panel-heading">
								<h3 class="panel-title">
									<?php _e('Get Directions', '12-step-meeting-list')?>
									<span class="glyphicon glyphicon-share-alt"></span>
								</h3>
							</a>
						</div>
						
						<?php 
						//ecommerce payment form
						if (tsml_accepts_payments() && !empty($meeting->contributions_api_key)) {?>
						
						<form id="payment">
							<input type="hidden" name="action" value="tsml_payment">
							<?php wp_nonce_field($tsml_nonce, 'tsml_nonce', false)?>
							<div class="panel panel-default panel-expandable">
								<div class="panel-heading">
									<h3 class="panel-title">
										<?php _e('Make a Contribution', '12-step-meeting-list')?>
										<span class="glyphicon glyphicon-chevron-left"></span>
									</h3>
								</div>
								<ul class="list-group">
									<li class="list-group-item">
									    <?php _e('Donations go to the group treasurer. For a receipt, enter your email address. It will be kept confidential.', '12-step-meeting-list')?></p>
									</li>
									<li class="list-group-item list-group-item-form">
										<input type="text" name="name" placeholder="<?php _e('Name (optional)', '12-step-meeting-list')?>">
									</li>
									<li class="list-group-item list-group-item-form">
										<input type="text" name="email" placeholder="<?php _e('Email (optional)', '12-step-meeting-list')?>">
									</li>
									<li class="list-group-item list-group-item-form">
										<input type="number" name="amount" id="amount" step="1" value="2">
									</li>
									<li class="list-group-item list-group-item-card credit-card">
										<div id="card-element"></div>
										<div id="card-errors" role="alert"></div>
									</li>
									<li class="list-group-item list-group-item-form credit-card">
										<button type="submit"><?php _e('Pay with Credit Card', '12-step-meeting-list')?></button>
									</li>
									<li class="list-group-item list-group-item-form apple-pay">
										<button type="submit"></button>
									</li>
								</ul>
							</div>
						</form>

						<?php }?>
						
						<div class="panel panel-default">
							<ul class="list-group">
								<li class="list-group-item meeting-info">
									<h3 class="list-group-item-heading"><?php _e('Meeting Information', '12-step-meeting-list')?></h3>
									<?php 
									echo '<p class="meeting-time"' . ($startDate ? ' itemprop="startDate" content="' . $startDate . '"' : '') . '>';
									echo tsml_format_day_and_time($meeting->day, $meeting->time);
									if (!empty($meeting->end_time)) {
										/* translators: until */
										echo __(' to ', '12-step-meeting-list'), tsml_format_time($meeting->end_time);
									}
									echo '</p>';
									if (count($meeting->types)) {?>
										<ul class="meeting-types">
										<?php foreach ($meeting->types as $type) {?>
											<li><i class="glyphicon glyphicon-ok"></i> <?php echo $type?></li>
										<?php }
										echo '</ul>';
										if (!empty($meeting->type_description)) {
											echo '<p class="meeting-type-description">' . $meeting->type_description . '</p>';
										}
									}
										
									if (!empty($meeting->notes)) {
										echo '<section class="meeting-notes">' . wpautop($meeting->notes) . '</section>';
									}
									?>
								</li>
								<?php
								if (!empty($meeting->location_id)) {
									$location_info = '<div itemprop="location" itemscope itemtype="http://schema.org/Place">
										<h3 class="list-group-item-heading">' . $meeting->location . '</h3>';
									
									if ($other_meetings = count($meeting->location_meetings) - 1) {
										$location_info .= '<p class="location-other-meetings">' . sprintf(_n('%d other meeting at this location', '%d other meetings at this location', $other_meetings, '12-step-meeting-list'), $other_meetings) . '</p>';
									}
									
									$location_info .= '<p class="location-address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">' . tsml_format_address($meeting->formatted_address) . '</p>';
	
									if (!empty($meeting->location_notes)) {
										$location_info .= '<section class="location-notes">' . wpautop($meeting->location_notes) . '</section>';
									}
									
									if (!empty($meeting->region) && !strpos($meeting->formatted_address, $meeting->region)) {
										$location_info .= '<p class="location-region">' . $meeting->region . '</p>';
									}
									
									$location_info .= '</div>';
	
									echo tsml_link(
										get_permalink($meeting->post_parent), 
										$location_info, 
										'tsml_meeting', 
										'list-group-item list-group-item-location'
									);
								}

								if (!empty($meeting->group_id)) {?>
									<li class="list-group-item list-group-item-group">
										<h3 class="list-group-item-heading"><?php echo $meeting->group?></h3>
										<?php if (!empty($meeting->group_notes)) {?>
											<section class="group-notes"><?php echo wpautop($meeting->group_notes)?></section>
										<?php }
										if (!empty($meeting->district)) {?>
											<section class="group-district"><?php echo $meeting->district?></section>
										<?php }											
										if (!empty($meeting->website)) {?>
											<p class="group-website">
												<a href="<?php echo $meeting->website?>" target="_blank"><?php echo $meeting->website?></a>
											</p>
										<?php }
										if (!empty($meeting->email)) {?>
											<p class="group-email">
												<a href="mailto:<?php echo $meeting->email?>"><?php echo $meeting->email?></a>
											</p>
										<?php }
										if (!empty($meeting->phone)) {?>
											<p class="group-phone">
												<a href="tel:<?php echo $meeting->phone?>"><?php echo $meeting->phone?></a>
											</p>
										</a>
										<?php }?>
									</li>
								<?php }?>
								<li class="list-group-item list-group-item-updated">
									<?php _e('Updated', '12-step-meeting-list')?>
									<?php the_modified_date()?>
								</li>
							</ul>
						</div>
	
						<?php if (!empty($tsml_feedback_addresses)) {?>
						<form id="feedback">
							<input type="hidden" name="action" value="tsml_feedback">
							<input type="hidden" name="meeting_id" value="<?php echo $meeting->ID?>">
							<?php wp_nonce_field($tsml_nonce, 'tsml_nonce', false)?>
							<div class="panel panel-default panel-expandable">
								<div class="panel-heading">
									<h3 class="panel-title">
										<?php _e('Request an Update', '12-step-meeting-list')?>
										<span class="glyphicon glyphicon-chevron-left"></span>
									</h3>
								</div>
								<ul class="list-group">
									<li class="list-group-item list-group-item-form">
										<input type="text" id="tsml_name" name="tsml_name" placeholder="<?php _e('Your Name', '12-step-meeting-list')?>" class="required">
									</li>
									<li class="list-group-item list-group-item-form">
										<input type="email" id="tsml_email" name="tsml_email" placeholder="<?php _e('Email Address', '12-step-meeting-list')?>" class="required email">
									</li>
									<li class="list-group-item list-group-item-form">
										<textarea id="tsml_message" name="tsml_message" placeholder="<?php _e('Message', '12-step-meeting-list')?>" class="required"></textarea>
									</li>
									<li class="list-group-item list-group-item-form">
										<button type="submit"><?php _e('Submit', '12-step-meeting-list')?></button>
									</li>										
								</ul>
							</div>
						</form>
						<?php }?>
						
					</div>
					<div class="col-md-8">
						<div id="map" class="panel panel-default"></div>
					</div>
				</div>
			</div>
		</div>
		
		<?php if (is_active_sidebar('tsml_meeting_bottom')) {?>
			<div class="widgets meeting-widgets meeting-widgets-bottom" role="complementary">
				<?php dynamic_sidebar('tsml_meeting_bottom')?>
			</div>
		<?php }?>
		
	</div>
</div>
<?php 
get_footer();
