jQuery(document).ready(function($)
{
  $('#postzip').change(function(){
    $(this).simpleUpload(ajax_object.ajaxurl+'?action=upload_posts_zip_file', {
      allowedExts: ["zip"],
      success: function(data){
        var dataObj = JSON.parse(data);
        var attachId = dataObj.id;
        $('#attachment-id').val(attachId);
        $('#postzip-submit').prop('disabled', false);
      },
      error: function(error){
        console.log('Error in upload');
      }
    });
  });
  $('#postzip-submit').click(function()
  {
    var selected = $("input[type='radio'][name='category']:checked");
    var selectedCategory = null;
    if (selected.length > 0) {
      selectedCategory = selected.val();
    }
    jQuery.ajax({
      type: 'POST',
      url: ajax_object.ajaxurl,
      data: {
        action: 'create_posts_batch',
        category: selectedCategory,
        attachId: $('#attachment-id').val()
      },
      success: function(data) {
        $('#batch-post-alert-success').text('Posts created for the current batch');
        $('#batch-post-alert-success').css('display', 'block');
      },
      error: function(data)
      {
        $('#batch-post-alert-error').text('Error creating posts for the current batch');
        $('#batch-post-alert-error').css('display', 'block');
      }
    });
  });
});
