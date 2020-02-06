// This uses presigned request to allow user to upload directly to s3.

(function ($, Drupal) {
  $.fn.uploadCallback = function(data) {
    console.log("doing upload cb");
    doUpload(data);
  }
  function doUpload(data) {
    $.ajax({
      method: "GET",
      url: "/rest/session/token",
      success: function(token) {
        var package = {};
        var theFormFile = $('#theFile').get()[0].files[0];
        package.file_name = [{"value":theFormFile.name}];
        $.ajax({
          url: "/aws-crr/v1/endpoint?_format=json",
          method: "POST",
          headers: {
            "X-CSRF-Token": token,
            "Accept": "application/json",
            "Content-Type": "application/json"
          },
          data: JSON.stringify(package),
          success: function(payload) {
            var presigned_url = payload.presigned_url;
            $.ajax({
              type: 'PUT',
              url: presigned_url,
              // Content type must much with the parameter you signed your URL with
              contentType: 'binary/octet-stream',
              // this flag is important, if not set, it will try to send data as a form
              processData: false,
              // the actual file is sent raw
              data: theFormFile,
              xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                  if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    // console.log(percentComplete);
                    $("#percent").text(percentComplete + "%");
                    if (percentComplete === 100) {
                      $("#percent").text("Done.");
                    }
                  }
                }, false);
                return xhr;
              },
              success: function(data, textStatus, jqXHR) {
                alert('File uploaded');
                console.log(payload);
              },
              error: function() {
                alert('File NOT uploaded');
                console.log( arguments);
              },
            });
          },
        })
      }
    });
  }


})(jQuery, Drupal);
