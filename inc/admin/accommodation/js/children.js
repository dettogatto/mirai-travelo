(function($){

  // Child cost

  function hide_remove_buttons_child(){
    var remove_buttons = $('.mt-child-remove-age');
    remove_buttons.each(function(){

      var child_cost_age = $(this).closest('.child_cost_age');
      var single_child_cost = $(this).closest('.single_child_cost');
      var brothers_num = single_child_cost.find('.child_cost_age').length;

      var last_single = $('.single_child_cost:last');
      var single_num = $('.single_child_cost').length;

      if(brothers_num > 1){
        $(this).show();
      } else if(single_num > 1 && last_single.is(single_child_cost)) {
        $(this).show();
      } else {
        $(this).hide();
      }

    });
  }

  // Add child cost triplet
  $('body').on('click', '.mt-child-add-age', function(e){

    e.preventDefault();

    var last_clone = $(this).closest('.child_cost').find('.child_cost_age:last');
    var new_clone = last_clone.clone();
    new_clone.insertAfter( last_clone );

    var inputs = new_clone.find("input");
    inputs.val("");

    inputs.each( function(index) {
      var name = $(this).attr( 'name' ).replace( /\[(\d+)\]\[(\d+)\]/, function( match, p1, p2 )
      {
        return '['+p1+'][' + ( parseInt( p2 ) + 1 ) + ']';
      } );

      // Update the "name" attribute
      $(this).attr( 'name', name );
    });

    hide_remove_buttons_child();

  });


  // Remove child cost triplet
  $('body').on('click', '.mt-child-remove-age', function(e){
    e.preventDefault();

    var child_cost_age = $(this).closest('.child_cost_age');
    var single_child_cost = $(this).closest('.single_child_cost');

    if(single_child_cost.find('.child_cost_age').length > 1){
      child_cost_age.remove();
    } else {
      single_child_cost.remove();
    }

    hide_remove_buttons_child();

  });


  $('body').on('click', '.mt-child-percentage-price', function(e){
    e.preventDefault();

    var percent = parseFloat( prompt("Insert percentage of adult price", "50"));
    var full_price = parseFloat( $('input[name="price_per_person"]').val() );
    var child_price = full_price * percent / 100;
    $(this).closest('.child_cost_age').find('input:last').val(child_price);
  });


  // Add child cost section
  $('.mt-child-add-child').click(function(e){
    e.preventDefault();

    var last_clone = $('.single_child_cost:last');
    var new_clone = last_clone.clone();
    new_clone.insertAfter( last_clone );

    var nofchild_obj = new_clone.find('.n_of_child');
    nofchild_obj.html(parseInt(nofchild_obj.html()) + 1);

    while(new_clone.find('.child_cost_age').length > 1){
      new_clone.find('.child_cost_age:last').remove();
    }

    var inputs = new_clone.find("input");
    inputs.val("");

    inputs.each( function(index) {
      var name = $(this).attr( 'name' ).replace( /\[(\d+)\]\[(\d+)\]/, function( match, p1, p2 )
      {
        return '[' + ( parseInt( p1 ) + 1 ) + ']['+p2+']';
      } );

      // Update the "name" attribute
      $(this).attr( 'name', name );
    });

    hide_remove_buttons_child();

  });

  $(document).ready(hide_remove_buttons_child);












  // Advanced Cost

  function assignAdultsNumbers(){
    var i = 1;
    $('.single-advanced-cost').each(function(){
      $(this).find('.n_of_adult').html(i);
      i++;
    });
  }

  // Add single advanced
  $('body').on('click', '.mt-add-advanced-cost', function(e){

    e.preventDefault();

    var last_clone = $('.single-advanced-cost:last');
    var new_clone = last_clone.clone();
    new_clone.insertAfter( last_clone );
    assignAdultsNumbers();

  });


  // Remove advanced cost triplet
  $('body').on('click', '.mt-advanced-price-remove-adult', function(e){
    e.preventDefault();

    var single_advanced_cost = $(this).closest('.single-advanced-cost');
    single_advanced_cost.remove();
    assignAdultsNumbers();

  });


  $('body').on('click', '.mt-advanced-percentage-price', function(e){
    e.preventDefault();

    var percent = parseFloat( prompt("Insert percentage of Adult #1 price", "50"));
    var full_price = parseFloat( $('input[name="price_per_person"]').val() );
    var advanced_price = full_price * percent / 100;
    $(this).closest('.single-advanced-cost').find('input:last').val(advanced_price);
  });

  // Change price_per_person accordingly

  $('body').on('input', '.single-advanced-cost:first input', function(e){
    $('input[name="price_per_person"]').val( $(this).val() );
  });

  // And vice versa
  $('body').on('input', 'input[name="price_per_person"]', function(e){
    $('.single-advanced-cost:first input').val( $(this).val() );
  });

  // Hide/show advanced cost
  function hideShowAdvancedCost(){
    if($('#advanced_cost_yn').prop("checked")){
      var first_input = $('.single-advanced-cost:first input');
      if(true || !first_input.val()){
        first_input.val($('input[name="price_per_person"]').val());
      }
      $('.advanced-cost').show();
      $('#price_per_person_container').hide();
    } else {
      $('.advanced-cost').hide();
      $('#price_per_person_container').show();
    }
  }

  $('body').on('change', '#advanced_cost_yn', hideShowAdvancedCost);
  $(document).ready(hideShowAdvancedCost);




  // Weekdays

  $('.mt-check-all-row').click(function(){
    $(this).closest('tr').find('input[type="checkbox"]').prop("checked", true);
  });
  $('.mt-uncheck-all-row').click(function(){
    $(this).closest('tr').find('input[type="checkbox"]').prop("checked", false);
  });

})(jQuery);
