{namespace spgallery=Speedprogs\SpGallery\ViewHelpers}

jQuery(document).ready(function($) {
  Galleria.loadTheme('{themeFile}');
  $('#{elementId}').galleria({
    dataSource: [
      <f:for each="{images}" as="image" iteration="iterator">
      <spgallery:raw>{</spgallery:raw>
        title       : '{image.original.name}',
        description : '{image.original.description}',
        image       : '<spgallery:location>{image.converted.small}</spgallery:location>',
        thumb       : '<spgallery:location>{image.converted.thumb}</spgallery:location>',
        big         : '<spgallery:location>{image.converted.large}</spgallery:location>'<f:if condition="{settings.showImageInfo}">,
        file        : '<spgallery:basename>{image.original.fileName}</spgallery:basename>',
        url         : '<spgallery:location>{image.original.fileName}</spgallery:location>',
        size        : '<spgallery:fileSize>{image.original.fileSize}</spgallery:fileSize>',
        type        : '<spgallery:case case="upper">{image.original.fileType}</spgallery:case>',
        width       : '{image.original.imageWidth}px',
        height      : '{image.original.imageHeight}px'</f:if>
      <spgallery:raw>}</spgallery:raw><f:if condition="{iterator.isLast}"><f:then></f:then><f:else>,</f:else></f:if>
      </f:for>
    ]<f:if condition="{settings.showImageInfo}">,
    extend: function(options) {
      var infoId = '{infoId}';
      var $infoBox = $('#' + infoId);
      this.bind('image', function(event) {
        var data = this.getData(event.index);
        var $link = $('<a></a>');
        $link.attr('href', data.url).attr('target', '_blank').html(data.file);
        $infoBox.find('td.' + infoId + '-file').html($link);
        $infoBox.find('td.' + infoId + '-size').html(data.size);
        $infoBox.find('td.' + infoId + '-type').html(data.type);
        $infoBox.find('td.' + infoId + '-width').html(data.width);
        $infoBox.find('td.' + infoId + '-height').html(data.height);
      });
    }</f:if><f:if condition="{options}">,</f:if>
    <f:for each="{options}" as="option" key="key" iteration="iterator">
      {key}: {option}<f:if condition="{iterator.isLast}"><f:then></f:then><f:else>,</f:else></f:if>
    </f:for>
  });
});