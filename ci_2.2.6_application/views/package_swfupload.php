	<!-- swfupload -->
	<script type="text/javascript" src="<?php echo base_url();?>includes/swfupload/2.2.0.5/swfupload.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>includes/swfupload/2.2.0.5/fileprogress.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>includes/swfupload/2.2.0.5/handlers.js"></script>
	<script type="text/javascript">
		<!--
			var swfu;
			dmcb.addLoadEvent(function () {
				swfu = new SWFUpload({
					// Backend Settings
					upload_url: "<?php echo base_url();?>upload/file/<?php echo $upload_url;?>",	// Relative to the SWF file, or absolute
					file_post_name: "swfuploadfile",
					post_params: {"sessionid": "<?php echo $this->session->userdata('session_id'); ?>"},	// These are the post params of the FIRST post to upload controller

					// File Upload Settings
					file_size_limit : "<?php echo $upload_size;?>",
					file_types : "<?php echo $upload_types;?>",
					file_types_description : "<?php echo $upload_description;?>",
					file_upload_limit : "1",
					file_queue_limit : "1",

					// Event handler settings
					swfupload_loaded_handler : swfUploadLoaded,
					file_dialog_start_handler: fileDialogStart,
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,

					// upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,

					// Button Settings
					button_image_url : "<?php echo base_url();?>includes/swfupload/2.2.0.5/browse.gif",	// Relative to the SWF file, or absolute
					button_placeholder_id : "spanButtonPlaceholder",
					<?php $size = getimagesize('includes/swfupload/2.2.0.5/browse.gif'); ?>
					button_width: <?php echo $size[0];?>,
					button_height: <?php echo $size[1]/4;?>,
					button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,

					// Flash Settings
					flash_url : "<?php echo base_url();?>includes/swfupload/2.2.0.5/swfupload.swf",

					custom_settings : {
						progress_target : "fsUploadProgress",
						upload_successful : false
					},

					// Debug settings
					debug: false
				});
			});
		-->
	</script>
	<style type="text/css" media="all">@import "<?php echo base_url();?>includes/swfupload/2.2.0.5/default.css";</style>
