﻿var SWFUpload;
if (typeof(SWFUpload) === "function") {

	SWFUpload.queue = {};
	
	SWFUpload.prototype.initSettings = (function (oldInitSettings) {
		return function () {
			if (typeof(oldInitSettings) === "function") {
				oldInitSettings.call(this);
			}
			
			this.customSettings.queue_cancelled_flag = false;
			this.customSettings.queue_upload_count = 0;
			
			this.customSettings.user_upload_complete_handler = this.settings.upload_complete_handler;
			this.customSettings.user_upload_start_handler = this.settings.upload_start_handler;
			this.settings.upload_complete_handler = SWFUpload.queue.uploadCompleteHandler;
			this.settings.upload_start_handler = SWFUpload.queue.uploadStartHandler;
			
			this.settings.queue_complete_handler = this.settings.queue_complete_handler || null;
		};
	})(SWFUpload.prototype.initSettings);

	SWFUpload.prototype.startUpload = function (fileID) {
		this.customSettings.queue_cancelled_flag = false;
		this.callFlash("StartUpload", [fileID]);
	
	};

	SWFUpload.prototype.cancelQueue = function () {
		this.customSettings.queue_cancelled_flag = true;
		this.stopUpload();
		
		var stats = this.getStats();
		while (stats.files_queued > 0) {
			this.cancelUpload();
			stats = this.getStats();
		}
	};
	
	SWFUpload.queue.uploadStartHandler = function (file) {
		var returnValue;
		if (typeof(this.customSettings.user_upload_start_handler) === "function") {
			returnValue = this.customSettings.user_upload_start_handler.call(this, file);
		}
		
		// To prevent upload a real "FALSE" value must be returned, otherwise default to a real "TRUE" value.
		returnValue = (returnValue === false) ? false : true;
		
		this.customSettings.queue_cancelled_flag = !returnValue;

		return returnValue;
	};
	
	SWFUpload.queue.uploadCompleteHandler = function (file) {
		var user_upload_complete_handler = this.customSettings.user_upload_complete_handler;
		var continueUpload;
		
		if (file.filestatus === SWFUpload.FILE_STATUS.COMPLETE) {
			this.customSettings.queue_upload_count++;
		}

		if (typeof(user_upload_complete_handler) === "function") {
			continueUpload = (user_upload_complete_handler.call(this, file) === false) ? false : true;
		} else if (file.filestatus === SWFUpload.FILE_STATUS.QUEUED) {
			// If the file was stopped and re-queued don't restart the upload
			continueUpload = false;
		} else {
			continueUpload = true;
		}
		
		if (continueUpload) {
			var stats = this.getStats();
			if (stats.files_queued > 0 && this.customSettings.queue_cancelled_flag === false) {
				this.startUpload();
			} else if (this.customSettings.queue_cancelled_flag === false) {
				this.queueEvent("queue_complete_handler", [this.customSettings.queue_upload_count]);
				this.customSettings.queue_upload_count = 0;
			} else {
				this.customSettings.queue_cancelled_flag = false;
				this.customSettings.queue_upload_count = 0;
			}
		}
	};
}