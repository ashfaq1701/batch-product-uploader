jQuery(document).ready(function($)
{
  $('#submit-copy-content').click(function()
  {
    var dataIds = $(this).attr('data-ids');
    $(this).prop('disabled', true);
    $.ajax({
      type: 'POST',
      url: ajax_object.ajaxurl,
      data: {
        action: 'submit_content_copy',
        ids: dataIds
      },
      success: function()
      {
        $('#batch-post-alert-success').text('Posts copied to target site');
        $('#batch-post-alert-success').css('display', 'block');
      },
      error: function()
      {
        $('#batch-post-alert-error').text('Could not copy post to target site');
        $('#batch-post-alert-error').css('display', 'block');
        $('#submit-copy-content').prop('disabled', false);
      }
    })
  });
});
