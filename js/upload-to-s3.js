// This uses presigned request to allow user to upload directly to s3.

(function ($, Drupal) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      $('#submitupload', context).click(function () {
        alert('Uploading now to ' + drupalSettings.presignedUrl);
        console.log(drupalSettings);
        var url = drupalSettings.presignedUrl;
        var params = "lorem=ipsum&name=alpha";
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);

        //Send the proper header information along with the request
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.send(params);
      });
    }
  };
})(jQuery, Drupal);
