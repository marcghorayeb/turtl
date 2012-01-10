function setupFileDrop() {
	// Local file drag-and-drop functionnality.
	$('#fileUpload').filedrop({
		url: '/accounts/uploadFile',
		paramname: 'file',
		data: {
			bankName: 'SG'
		},
		maxfiles: 1,
		maxfilesize: 5,
		error: function (err, file) {
			switch (err) {
				case 'BrowserNotSupported':
					alert('Browser does not support html5 drag and drop');
					break;
				case 'TooManyFiles':
					alert('TooManyFiles');
					// user uploaded more than 'maxfiles'
					break;
				case 'FileTooLarge':
					alert('FileTooLarge');
					// program encountered a file whose size is greater than 'maxfilesize'
					// FileTooLarge also has access to the file which was too large
					// use file.name to reference the filename of the culprit file
					break;
				default:
					alert('Error! '+err);
					break;
			}
		},
		dragOver: function() {
			// user dragging files over #dropzone
		},
		dragLeave: function() {
			// user dragging files out of #dropzone
		},
		docOver: function() {
			// user dragging files anywhere inside the browser document window
			$('#fileUpload').addClass('droppable');
		},
		docLeave: function() {
			// user dragging files out of the browser document window
			$('#fileUpload').removeClass('droppable');
		},
		drop: function() {
			// user drops file
			$('#fileUpload').removeClass('droppable');
		},
		uploadStarted: function(i, file, len){
			// a file began uploading
			// i = index => 0, 1, 2, 3, 4 etc
			// file is the actual file of the index
			// len = total files user dropped
			$('#fileUpload').removeClass('droppable');
			$('#notifications').jGrowl('Envoi en cours...');
		},
		uploadFinished: function(i, file, response, time) {
			// response is the data you got back from server in JSON format.
			$('#notifications').jGrowl('Mise à jour effectuée.');
		},
		progressUpdated: function(i, file, progress) {
			// this function is used for large files and updates intermittently
			// progress is the integer value of file being uploaded percentage to completion
		},
		speedUpdated: function(i, file, speed) {
			// speed in kb/s
		},
		rename: function(name) {
			// name in string format
			// must return alternate name as string
		},
		beforeEach: function(file) {
			// file is a file object
			// return false to cancel upload
		},
		afterAll: function() {
			// runs after all files have been uploaded or otherwise dealt with
			$('#fileUpload').removeClass('droppable');
			refreshAccountList();
		}
	});
}

jQuery(document).ready(function(){
	setupFileDrop();
});