// This uses presigned request to allow user to upload directly to s3.

(function ($, Drupal) {
  
  Drupal.behaviors.awsuploadtos3 = {
    attach: function (context, settings) {
      const form = $('#s3form', context)
      const url = form.attr('src')
      console.log(form);
      console.log(url);
      $('#submitupload', context).click(function () {
        $.ajax({
          method: "GET",
          url: "/rest/session/token",
          success: function(token) {
            var package = {};
            var theFormFile = $('#theFile').get()[0].files[0];
            console.log(theFormFile);
            package.title = [{"value":"title"}];
            package.type = [{"target_id":"article"}];
            package._links = {"type":{"href":"http://localhost/rest/type/node/article"}};
            package.file_name = [{"value":theFormFile.name}];
            console.log(token);
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
                  success: function() {
                    alert('File uploaded');
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
      });
    }
  }
})(jQuery, Drupal);
