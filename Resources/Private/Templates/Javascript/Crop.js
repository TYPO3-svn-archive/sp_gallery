{namespace spgallery=Tx_SpGallery_ViewHelpers}

jQuery(document).ready(function($) {
  var $element = $('#{elementId}');
  var $form    = $('#{formId}');
  $element.Jcrop({
    onSelect: function(coordinates) {
      $form.find('input.top').val(coordinates.y);
      $form.find('input.left').val(coordinates.x);
      $form.find('input.bottom').val(coordinates.y2);
      $form.find('input.right').val(coordinates.x2);
      $form.find('input.width').val(coordinates.w);
      $form.find('input.height').val(coordinates.h);
    }
  });
});