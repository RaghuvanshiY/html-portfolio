// $(document).on('click', '#updater-btn', function(e) {
//     if (confirm("Are you sure want to upgrade the system?")) {
//       e.preventDefault();
//       $.ajax({
//         url: base_url + 'updater/update',
//         type: "POST",
//         data: csrfName + '=' + csrfHash,
//         dataType: 'json',
//         beforeSend: function() {
//           // $('#result').fadeIn(100);
//           $('#updater-btn').html('Please Wait... System is being updated');
//           $('#updater-btn').prop('disabled', true);
//         },
//         success: function(result) {

//           csrfName = result['csrfName'];
//           csrfHash = result['csrfHash'];
//           console.log(result['error']);
//           if (result['error'] == false) {
//             $('#success-msg').removeClass('d-none');
//             $('#updater-btn').hide();
//           } else {
//             $('#notsuccess-msg').removeClass('d-none');
//             $('#updater-btn').hide();
//           }

//         }
//       });
//     }
//   });
Dropzone.autoDiscover = false;
var myDropzone = new Dropzone("#update-file-dropzone", {
    url: base_url + 'updater/upload',
    autoProcessQueue: false,
    acceptedFiles: "application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip,.zip",
    parallelUploads: 1,
    uploadMultiple: false,
    dictDefaultMessage: dictDefaultMessage,
    addRemoveLinks: true,
    dictRemoveFile: 'x',

});

$(document).on('click', '#updater-btn', function (e) {
    if (confirm("Are you sure want to upgrade the system?")) {
        $('#updater-btn').html('Please Wait... System is being updated');
        $('#updater-btn').prop('disabled', true);
        myDropzone.processQueue();
    }
});
myDropzone.on("addedfile", function (file) {
    var i = 0;
    if (this.files.length) {
        var _i, _len;
        for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) // -1 to exclude current file
        {
            if (this.files[_i].name === file.name && this.files[_i].size === file.size && this.files[_i].lastModifiedDate.toString() === file.lastModifiedDate.toString()) {
                this.removeFile(file);
            } else if (this.files[4] != null) {
                this.removeFile(file);

            }
            i++;
        }
    }

});

myDropzone.on('sending', function (file, xhr, formData) {
    formData.append(csrfName, csrfHash);
});
myDropzone.on("queuecomplete", function (file) {
    location.reload();
});