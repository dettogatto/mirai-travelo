(function($){

  function hide_remove_buttons(){
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

    hide_remove_buttons();

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

    hide_remove_buttons();

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

    hide_remove_buttons();

  });

  $(document).ready(hide_remove_buttons);




  $('.mt-check-all-row').click(function(){
    $(this).closest('tr').find('input[type="checkbox"]').prop("checked", true);
  });
  $('.mt-uncheck-all-row').click(function(){
    $(this).closest('tr').find('input[type="checkbox"]').prop("checked", false);
  });

})(jQuery);
