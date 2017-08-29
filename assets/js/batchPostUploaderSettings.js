jQuery(document).ready(function($)
{
  $('#category_select').change(function()
  {
    var catId = $(this).val();
    jQuery.ajax({
      type: 'GET',
      url: ajax_object.ajaxurl,
      data: {
        action: 'get_pricing_setting',
        cat_id: catId
      },
      success: function(data)
      {
        if(data != 0)
        {
          $('.pricing-top-container').remove();
          $('.settings-top-container').append(data);
          $('#category_id').val(catId);
        }
      }
    });
  });
});

function addNewAdjustment()
{
  var adjItem = jQuery('.adjustment-container-item').first();
  var adjItemClone = jQuery(adjItem).clone();
  jQuery(adjItemClone).insertBefore('#add-adjustment');
}

function savePricing()
{
  var categoryId = jQuery('#category_id').val();
  var basePrice = jQuery('.base-price').val();
  var adjustments = [];
  jQuery('.adjustment-container-item').each(function()
  {
    var tagId = jQuery(this).find('.tag-select').first().val();
    var tagAdj = jQuery(this).find('.tag-adjustment').first().val();
    adjustments.push({
      tagId: tagId,
      tagAdj: tagAdj
    });
  });
  var catObj = {
    catId: categoryId,
    basePrice: basePrice,
    adjustments: adjustments
  };
  jQuery.ajax({
    type: 'POST',
    url: ajax_object.ajaxurl,
    data: {
      action: 'create_pricing_setting',
      setting: JSON.stringify(catObj)
    },
    success: function(data)
    {
      jQuery('#batch-post-settings-alert-success').text('Price setting for category is created.');
      jQuery('#batch-post-settings-alert-success').css('display', 'block');
    },
    error: function(data)
    {
      jQuery('#batch-post-settings-alert-error').text('Could not create price setting for category.');
      jQuery('#batch-post-settings-alert-error').css('display', 'block');
    }
  });
}
