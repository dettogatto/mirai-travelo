/**
 * Accommodation Scripts
 */

jQuery(document).ready(function($) {
	"use strict";

	$("#check_availability_form").submit(function(e) {
		e.preventDefault();
		var date_from_obj = $(this).find('input[name="date_from"]');
		var date_to_obj = $(this).find('input[name="date_to"]');
		var booking_data = '';

		//form validation
		var date_from  = date_from_obj.val();
		if (! date_from) {
			trav_field_validation_error([date_from_obj], acc_data.msg_wrong_date_2, $('#check_availability_form .alert-error'));

			$('html, body').animate({
					scrollTop: $('#check_availability_form .alert').offset().top - 150
				}, 'slow');

			return false;
		}
		var date_to  = date_to_obj.val();
		if (! date_to) {
			trav_field_validation_error([date_to_obj], acc_data.msg_wrong_date_3, $('#check_availability_form .alert-error'));

			$('html, body').animate({
					scrollTop: $('#check_availability_form .alert').offset().top - 150
				}, 'slow');

			return false;
		}

    if(date_format === "DD/MM/YYYY"){
      date_from = date_from.split('/').reverse().join('-');
      date_to = date_to.split('/').reverse().join('-');
    }

		var one_day=1000*60*60*24;
		var date_from_date = new Date(date_from);
		var date_to_date = new Date(date_to);
		var today = new Date();

    console.log("Date Format:" + date_format);
    console.log("Date From: " + date_from);
    console.log("Date To: " + date_to);
    console.log("Parsed Date From: " + date_from_date);
    console.log("Parsed Date To: " + date_to_date);

		today.setDate(today.getDate() - 1);
		if (date_from_date < today) {
			trav_field_validation_error([$('input[name="date_from"]')], acc_data.msg_wrong_date_6, $('#check_availability_form .alert-error'));

			$('html, body').animate({
					scrollTop: $('#check_availability_form .alert').offset().top - 150
				}, 'slow');

			return false;
		}
		date_from_date = date_from_date.getTime();
		date_to_date = date_to_date.getTime();

		/*if (date_from_date + one_day * acc_data.minimum_stay - date_to_date > 0) {
			var msg = acc_data.msg_wrong_date_5;
			if (date_from_date >= date_to_date) { msg = acc_data.msg_wrong_date_4; }
			//trav_field_validation_error([date_from_obj,date_to_obj], msg, $('#check_availability_form .alert-error'));
			return false;
		}*/

		booking_data = $("#check_availability_form").serialize();

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: booking_data,
			success: function(response){
				if (response.success == 1) {
					$('.search-available-rooms').html(response.result);

					$('html, body').animate({
						scrollTop: $('.search-available-rooms').offset().top - 200
					}, 'slow');

					setTimeout(
						function(){
							$('.search-available-rooms .single-travel-item .featured-imgs').each(function(index) {
								var $this = $(this);

								$this.slick({
									draggable: true,
									dots: true,
									infinite: false,
									slidesToShow: 1,
									slidesToScroll: 1,
								});
							})
						},
						100 );

					var itemCount = 0,
						itemInterval;

					itemInterval = setInterval(function() {
						$('.search-available-rooms .single-travel-item-wrap').eq(itemCount).addClass('item-loaded');
						itemCount++;
					}, 50);

					$('a.search-edit-btn').on('click', function(e) {
						e.preventDefault();

						$('html, body').animate({
							scrollTop: $($(this).attr('href')).offset().top - 100
						}, 'slow');
					});
				} else {
					alert(response.result);
				}
			}
		});
		return false;
	});

	 // book now action
    $('.room-list').on('click', '.btn-book-now', function(e) {
        e.preventDefault();
        if (acc_data.booking_url) {
            var room_type_id = $(this).data('room-type-id');
            $('input[name="action"]').remove();
            var booking_data = $("#check_availability_form").serialize();
            var form = $('<form method="get" action="' + acc_data.booking_url + '"></form>');
            if ( acc_data.lang ) {
                form.append('<input type="hidden" name="lang" value="' + acc_data.lang + '">');
            }
            form.append('<input type="hidden" name="booking_data" value="' + escape(booking_data + '&room_type_id=' + room_type_id) + '">');

            $("body").append(form);
            form.submit();
        } else {
            alert(acc_data.msg_no_booking_page);
        }
        return false;
    });

	//reviews ajax loading
	$('.more-acc-review').click(function() {

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				'action': 'acc_get_more_reviews',
				'accommodation_id' : acc_data.acc_id,
				'last_no' : $('.guest-review').length
			},
			success: function(response){
				if (response == '') {
					$('.more-review').remove();
				} else {
					$('.guest-reviews').append(response);
				}
			}
		});
		return false;
	});

	// Star Rating
	$('.review-form-inner .special-rating').each(function(index) {
		var $this = $(this),
			clickStar = $this.find('p.stars a');

		clickStar.on('click', function(e) {
			e.preventDefault();

			var $star   	= $(this),
				$rating 	= $(this).parent().parent().find( '.rating_detail_hidden' ),
				$container 	= $(this).closest( '.stars' );

			$rating.val( $star.text() );
			$star.siblings('a').removeClass( 'active' );
			$star.addClass('active');
			$container.addClass('selected');

			var total = 0;
			$('.rating_detail_hidden').each( function() {
				total += parseFloat( $(this).val() || 0 );
			});
			var review_marks = Object.keys(acc_data.review_labels);
			review_marks.sort(function(a, b){return b-a});

			$.each(review_marks, function(index, review_mark) {
				if ( review_mark < total / 6 ) {
					$('.form-title-part .ribbon').html(acc_data.review_labels[review_mark]);
					return false;
				}
			});

			$('input[name="review_rating"]').val(total / 6);

			return false;
		});
	});

	$('#review-form').validate({
		rules: {
			review_rating_detail: { required: true },
			pin_code: { required: true},
			booking_no: { required: true},
			review_title: { required: true},
			review_text: { required: true},
		}
	});

	$('#review-form').submit(function() {
		//form validation
		var review_flag = true;
		$('.rating_detail_hidden').each(function() {
			if (! $(this).val() || ($(this).val() == 0)) {
				review_flag = false;
				return false;
			}
		});

		if (! review_flag) {
			$('#hotel-write-review .alert').removeClass('alert-success');
			$('#hotel-write-review .alert').addClass('alert-error');
			var msg = "Please provide ratings for every category greater than 1 star.";
			trav_field_validation_error([$('.individual-rating-part')], msg, $('#hotel-write-review .alert'));

			$('html, body').animate({
				scrollTop: $('.write-review-form .alert').offset().top - 150
			}, 'slow');

			return false;
		}

		$('#review-form .validation-field').each(function() {
			if (! $(this).val()) {
				var msg = $(this).data('error-message');
				trav_field_validation_error([$(this)], msg, $('#hotel-write-review .alert-error'));

				$('html, body').animate({
					scrollTop: $('.write-review-form .alert').offset().top - 150
				}, 'slow');

				review_flag = false;
				return false;
			}
		});

		if (! review_flag) {
			return false;
		}

		var ajax_data = $("#review-form").serialize();
		jQuery.ajax({
			url: ajaxurl,
			type: "POST",
			data: ajax_data,
			success: function(response){
				if (response.success == 1) {
					$('#hotel-write-review .alert').addClass('alert-success');
					$('#hotel-write-review .alert').removeClass('alert-error');
					trav_show_modal(1, response.title, response.result);
					/*var msg = 'Thank you! Your review has been submitted successfully.';
					trav_field_validation_error([], msg, $('#hotel-write-review .alert'));
					$('.submit-review').hide();*/
				} else {
					$('#hotel-write-review .alert').removeClass('alert-success');
					$('#hotel-write-review .alert').addClass('alert-error');
					trav_show_modal(0, response.result, '');
					/*var msg = response.result;
					trav_field_validation_error([], msg, $('#hotel-write-review .alert'));*/
				}
			}
		});

		return false;
	});

	var error_timer;
	//check field validation
	function trav_field_validation_error(objs, msg, alert_field) {
	    for (var i = 0; i < objs.length; ++i) {
	        objs[i].closest('.validation-field').addClass('error-field');
	    }

	    alert_field.find('.message').html(msg);
	    alert_field.fadeIn(300);
	    clearTimeout(error_timer);
	    error_timer = setTimeout(function() {
	        alert_field.fadeOut(300);
	        $('.validation-field').removeClass('error-field');
	    }, 5000);
	}

	function trav_show_modal(success, title, content) {
		var modal;

		if (success == 1) {
			if ($('#travelo-success').length > 0) {
				modal = $('#travelo-success');
			} else {
				modal = $('<div id="travelo-success" class="travelo-modal-box travelo-box"><div class="travelo-modal-head"><p class="travelo-modal-icon"><i class="soap-icon-check circle"></i></p><h4 class="travelo-modal-title"></h4></div><div class="travelo-modal-content"></div></div>').appendTo('footer');
			}
		} else {
			if ($('#travelo-failure').length > 0) {
				modal = $('#travelo-failure');
			} else {
				modal = $('<div id="travelo-failure" class="travelo-modal-box travelo-box"><div class="travelo-modal-head"><p class="travelo-modal-icon"><i class="soap-icon-notice circle"></i></p><h4 class="travelo-modal-title"></h4></div><div class="travelo-modal-content"></div></div>').appendTo('footer');
			}
		}
		modal.find('.travelo-modal-title').html(title);
		modal.find('.travelo-modal-content').html(content);
		if ($("#soap-popupbox").length < 1) {
			$("<div class='opacity-overlay' id='soap-popupbox' tabindex='-1'><div class='container'><div class='popup-wrapper'><div class='popup-content'></div></div></div></div>").appendTo("body");
		}
		$("#soap-popupbox .popup-content").children().hide();
		modal.appendTo($("#soap-popupbox .popup-content"));
		modal.show();
		$("#soap-popupbox").fadeIn(function() {
			modal.find(".input-text").eq(0).focus();
		});
	}

	$('a.review-write-link').on('click', function(e) {
		e.preventDefault();

		$('html, body').animate({
			scrollTop: $($(this).attr('href')).offset().top - 100
		}, 'slow');
	});

  // By Mirai

  $("#vacant-main-form").submit(function(e) {
    $(this).find('input').each(function(index) {
      var inputName = $(this).attr('name');
      $("#check_availability_form").find('input[name="'+inputName+'"]').val($(this).val());
      if(inputName == "kids"){
        mt_show_child_age_inputs();
      }
    });

    var child_ages = $(this).find('.single-child-age').map(function(){
      return $(this).find("input").val();
    }).toArray();


    $("#check_availability_form").find('input[name="child_ages[]"]').each(function(){
      $(this).val(child_ages.shift());
    });

    if ( $("#check_availability_form").find('input[name="date_from"]').val() != "" && $("#check_availability_form").find('input[name="date_to"]').val() != "" ) {
      $("#check_availability_form").find('#single-availability-dates').data('daterangepicker').setStartDate( $("#check_availability_form").find('input[name="date_from"]').val() );
      $("#check_availability_form").find('#single-availability-dates').data('daterangepicker').setEndDate( $("#check_availability_form").find('input[name="date_to"]').val() );
      $("#check_availability_form").find('#single-availability-dates').val( $("#check_availability_form").find('input[name="date_from"]').val() + ' - ' + $("#check_availability_form").find('input[name="date_to"]').val() );
      $("#check_availability_form").find('#single-availability-dates').data('daterangepicker').updateView()

      $("#check_availability_form").submit();
    }

    return false;
  });

});
