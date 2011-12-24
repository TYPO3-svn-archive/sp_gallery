{namespace spgallery=Tx_SpGallery_ViewHelpers}

jQuery(document).ready(function($) {
  var $image = $('#{elementId}');
  var $form  = $('#{formId}');
  var width  = $form.find('input.imageWidth').val();
  var height = $form.find('input.imageHeight').val();

  $form.find('input.factorX').val(width / $image.width());
  $form.find('input.factorY').val(height / $image.height());

  $image.Jcrop({
    onSelect: function(coordinates) {
      $form.find('input.top').val(coordinates.y);
      $form.find('input.left').val(coordinates.x);
      $form.find('input.width').val(coordinates.w);
      $form.find('input.height').val(coordinates.h);
      $form.find('input.save-button').hide();
      $form.find('input.preview-button').show();
    },
    onRelease : function() {
      $form.find('input.top').val(0);
      $form.find('input.left').val(0);
      $form.find('input.width').val(0);
      $form.find('input.height').val(0);
      $form.find('input.preview-button').hide();
      $form.find('input.save-button').show();
    }
  });
});