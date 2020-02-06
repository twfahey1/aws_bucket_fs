// This uses presigned request to allow user to upload directly to s3.

(function ($, Drupal) {
  $.fn.uploadCallback = function(form_element, local_file_path, bucket, path_to_store) {
    console.log("doing upload of " + local_file_path);
    doUpload(form_element, local_file_path, bucket, path_to_store);
  }
  function doUpload(form_element, local_file_path, bucket, path_to_store) {
    $.ajax({
      method: "GET",
      url: "/rest/session/token",
      success: function(token) {
        var package = {};
        package.file_name = [{"value":local_file_path}];
        package.bucket = [{"value":bucket}];
        package.path_to_store = [{"value":path_to_store}];
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
            var form_element_selected = $(form_element).get()[0].files[0];
            $.ajax({
              type: 'PUT',
              url: presigned_url,
              // Content type must much with the parameter you signed your URL with
              contentType: 'binary/octet-stream',
              // this flag is important, if not set, it will try to send data as a form
              processData: false,
              // the actual file is sent raw
              data: form_element_selected,
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
                console.log(arguments);
                console.log(data);
                console.log(textStatus);
                console.log(jqXHR);
                $('#aws-file-edit-form').trigger('submit');
                $('#aws-file-add-form').trigger('submit');
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
