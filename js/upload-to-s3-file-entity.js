// This uses presigned request to allow user to upload directly to s3.

(function ($, Drupal) {

  function submitEntityForm() {
    $('#aws-file-edit-form').trigger('submit');
    $('#aws-file-add-form').trigger('submit');
  }

  function doUpload(form_element, local_file_path, bucket, path_to_store) {
    return new Promise((resolve, reject) => {
      $.ajax({
        method: "GET",
        url: "/rest/session/token",
        success: function(token) {
          var package = {};
          package.operation = [{"value":'create'}];
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
                  resolve(data);
                },
                error: function(error) {
                  alert('File NOT uploaded');
                  console.log( arguments);
                  reject(error);
                },
              });
            },
          })
        }
      });
    })
  }
  $.fn.uploadCallback = function(form_element, local_file_path, bucket, path_to_store) {
    console.log("doing upload of " + local_file_path);
    $.when(doUpload(form_element, local_file_path, bucket, path_to_store)).then(function( data, textStatus, jqXHR ) {
      console.log("uploadCallback done");
      submitEntityForm();
    })
  }

  $.fn.renameCallback = function($region, $original_bucket, $new_bucket, $original_key, $new_key) {
    console.log("doing rename of " + $original_key + " to " + $new_key);
    $.when(doRename($region, $original_bucket, $new_bucket, $original_key, $new_key)).then(function( data, textStatus, jqXHR ) {
      console.log("rename done");
    })
  }
  function doRename($region, $original_bucket, $new_bucket, $original_key, $new_key) {
    return new Promise((resolve, reject) => {

      $.ajax({
        method: "GET",
        url: "/rest/session/token",
        success: function(token) {
          var package = {};
          package.operation = [{"value":'rename'}];
          package.region = [{"value": $region}];
          package.original_bucket = [{"value": $original_bucket}];
          package.new_bucket = [{"value": $new_bucket}];
          package.original_key = [{"value": $original_key}];
          package.new_key = [{"value": $new_key}];
          $.ajax({
            url: "/aws-crr/v1/endpoint?_format=json",
            method: "POST",
            headers: {
              "X-CSRF-Token": token,
              "Accept": "application/json",
              "Content-Type": "application/json"
            },
            data: JSON.stringify(package),
            success: function(data) {
              console.log(payload);
              resolve(data);
            },
            failure: function(error) {
              console.log(payload);
              reject(error);
            }
          })
        }
      });
    })
  }

  $.fn.deleteCallback = function($region, $bucket, $key) {
    console.log("doing delete of " + $key);
    $.when(doDelete($region, $bucket, $key)).then(function( data, textStatus, jqXHR ) {
      console.log("done delete");
    });
  }

  function doDelete($region, $bucket, $key) {
    var deleteAction = $.ajax({
      method: "GET",
      url: "/rest/session/token",
      success: function(token) {
        var package = {};
        package.operation = [{"value":'delete'}];
        package.region = [{"value": $region}];
        package.bucket = [{"value": $bucket}];
        package.key = [{"value": $key}];
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
            console.log(payload);
            resolve(data);
          },
          failure: function(error) {
            console.log(payload);
            reject(error);
          }
        })
      }
    });
    return deleteAction;
  }

  $.fn.deleteAndUpload = function($file_form_selector, $region, $original_bucket, $new_bucket, $original_key, $new_key) {
    console.log("Doing upload & delete")
    $.when(doUpload($file_form_selector, $region, $new_bucket, $new_key)).then(function( data, textStatus, jqXHR ) {
      if ($new_key == $original_key && $new_bucket == $original_bucket) {
        console.log("Not doing a delete, cause file & bucket paths are the same.")
        submitEntityForm()
      }
      else {
        console.log("Going to delete old file.")
        $.when(doDelete($region, $original_bucket, $original_key)).then(
          submitEntityForm()
        )
      }
    });
  }


})(jQuery, Drupal);
