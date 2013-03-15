function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("等待上传...");
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}

}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("对不起，每次最多允许选择"+message+"个文件");
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("文件太大.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("不允许上传0字节的文件.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("未知文件类型.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("未知错误！");
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById('btnUpload').disabled = false;
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
			
		/* I want auto start the upload and I can do that here */
		//this.startUpload();
	} catch (ex)  {
        this.debug(ex);
	}
}

function uploadStart(file) {
	ShadeDiv.Show('AutoPostPrompt','300','30');//保护上传弹出层
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("上传中...");
		progress.toggleCancel(true, this);
	}
	catch (ex) {}

	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		progress.setStatus("上传中...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		
		//if (serverData.substring(1, 5) === "suc:") {
		if (serverData.indexOf("suc")!=-1) {
			var fileinfo = serverData.substring(8).split(",");
			//alert(fileinfo);
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.setText(""+fileinfo[1]+"");
			var status = "文件上传成功！ <br />";
			status += "图片大小："+parseFloat(parseInt(fileinfo[2])/1024).toFixed(2)+ "K <br />";
			status += "预览地址：/"+fileinfo[0];
			progress.setComplete();
			progress.setStatus(status);
			progress.toggleCancel(false);
			document.getElementById('imglist').innerHTML += '<li><img src=/'+fileinfo[3]+'></li>';
			document.getElementById('hiddenKey').value = fileinfo[4];

			getimgtip();
		}else{
			
			var progress = new FileProgress(file, this.customSettings.progressTarget);
			progress.setError();
			progress.setStatus("上传失败："+serverData.substring(8));
			progress.toggleCancel(false);
			document.getElementById('imglist').innerHTML += '<img src=/'+fileinfo[0]+fileinfo[1]+'><br/>';

		}

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("文件上传失败: " + message);
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("文件上传失败");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("服务器IO错误");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("安全错误");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("文件大小超过限制");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("验证失败，上传已被跳过");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById('btnUpload').disabled = true;
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("已取消上传");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("已停止上传");
			break;
		default:
			progress.setStatus("未知错误: " + errorCode);
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		document.getElementById('btnUpload').disabled = true;
		//document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	var status = document.getElementById("divStatus");
	status.innerHTML = "您已经成功上传了" + numFilesUploaded + " 张图片.";
	subphotoupdate('aa');

}
