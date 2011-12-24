{namespace spgallery=Tx_SpGallery_ViewHelpers}

jQuery(document).ready(function($) {
  var $image = $('#{elementId}');
  var $form  = $('#{formId}');
  var width  = $image.width();
  var height = $image.height();
  $image.Jcrop({
    onSelect: function(coordinates) {
      $form.find('input.top').val(coordinates.y);
      $form.find('input.left').val(coordinates.x);
      $form.find('input.width').val(coordinates.w);
      $form.find('input.height').val(coordinates.h);
      $form.find('input.imgHeight').val(height);
      $form.find('input.imgWidth').val(width);
    }
  });
});