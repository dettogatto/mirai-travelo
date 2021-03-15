/**
 * Theme Scripts
 */

var stGlobals = {};
stGlobals.isMobile  = (/(Android|BlackBerry|iPhone|iPod|iPad|Palm|Symbian)/.test(navigator.userAgent));
stGlobals.isMobileWebkit = /WebKit/.test(navigator.userAgent) && /Mobile/.test(navigator.userAgent);
stGlobals.isIOS = (/iphone|ipad|ipod/gi).test(navigator.appVersion);

jQuery(document).ready(function($) {
	"use strict";

	// Header Sticky Setting
	var headerHeight = $('header.main-header').outerHeight();

	$(window).scroll(function(e) {
		if ( $(this).scrollTop() > headerHeight ) {
			$('.travelo-sticky-header').addClass('page-scroll');
		} else {
			$('.travelo-sticky-header').removeClass('page-scroll');
		}
	});

	// Mobile Navigation Settings
	$('.header-mobile-nav').off('click').on('click', function(e) {
		e.preventDefault();
		$('body').toggleClass('mobile-nav-active');

		return false;
	});

	$(document).on('click', 'body', function(e) {
		if ( $('.mobile-nav-active .mobile-nav').length > 0 && ! $(e.target).is('.mobile-nav-active .mobile-nav, .mobile-nav-active .mobile-nav *') ) {
			$('body').removeClass('mobile-nav-active');
		}
	});

	$('.mobile-nav .close-btn .close-btn-link').on('click', function(e) {
		e.preventDefault();
		$('body').removeClass('mobile-nav-active');
	});

	$('.travelo-mobile-nav .menu-item-has-children').append('<div class="drop-nav"><i class="fas fa-chevron-down"></i></div>');

	$('.travelo-mobile-nav').on('click', '.drop-nav', function(e) {
		e.stopPropagation();

		$(this).parent().toggleClass('active-submenu').children('.sub-menu-dropdown').toggleClass('opened');

		if ( $(this).parent().children('.sub-menu-dropdown').hasClass('opened') ) {
			$(this).parent().children('.sub-menu-dropdown').slideDown(300);
		} else {
			$(this).parent().children('.sub-menu-dropdown').slideUp(300);
		}
	});

	// Hero Carousel Slider
	$('.hero-slider-wrap').owlCarousel({
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
		items: 1,
		loop: true,
		autoplay: true,
		nav: false,
		dots: true,
		mouseDrag: false,
		touchDrag: false
	});

	$('.destinations-masonry-grid').each( function(){
		var child_count = $(this).find('.single-destination').length;
		if ( child_count > 7 ) {
			$(this).find('.single-destination').attr( 'data-col', 'seven-more' );
		} else {
			$(this).find('.single-destination').attr( 'data-col', child_count );
		}
	});

	// Daterange Picker
	var rangePickerEle = $('.check-in-out-wrap input[name="datetimes"]'),
		today = new Date(),
		dd = today.getDate(),
		mm = today.getMonth() + 1,
		yyyy = today.getFullYear().toString();


	if ( dd < 10 ) {
		dd='0' + dd;
	}

	if ( mm < 10 ) {
		mm='0' + mm;
	}

	var today = '';
	if ( rangePickerEle.attr( 'format' ) == 'DD/MM/YYYY') {
		today = dd + '/' + mm + '-' + yyyy;
	} else if ( rangePickerEle.attr( 'format' ) == 'MM/DD/YYYY') {
		today = mm + '/' + dd + '-' + yyyy;
	} else {
		today = yyyy + '-' + mm + '-' + dd;
	}

	rangePickerEle.each(function(index) {
		var $this = $(this),
			dateInVal = $this.siblings('.date-in').find('.date-value'),
			dateOutVal = $this.siblings('.date-out').find('.date-value'),
			clickElement = $this.parents('.check-in-out-wrap').find('.form-control');

		$this.daterangepicker({
			autoApply: true,
			minDate: today,
			locale: {
				format: date_format,
				separator: ' - ',
			}
		}, function( start, end, label ) {
			var startDate = start.format( date_format ),
				endDate = end.format(date_format);

			dateInVal.text( startDate );
			dateOutVal.text( endDate );
			$this.parent().find('input[name="date_from"]').val( startDate );
			$this.parent().find('input[name="date_to"]').val( endDate );
		});

		if ( $this.parent().find('input[name="date_from"]').val() != undefined && $this.parent().find('input[name="date_from"]').val() != "" ) {
			$this.data('daterangepicker').setStartDate($this.parent().find('input[name="date_from"]').val());
		}

		if ( $this.parent().find('input[name="date_to"]').val() != undefined && $this.parent().find('input[name="date_to"]').val() != "" ) {
			$this.data('daterangepicker').setEndDate($this.parent().find('input[name="date_to"]').val());
		}

		clickElement.on('click', function(e) {
			$this.click();
		});
	});

	var detailPicker = $('#single-availability-dates').daterangepicker({
			parentEl: "#single-availability-dates-container",
			autoApply: true,
			minDate: today,
			locale: {
				format: date_format,//'YYYY-MM-DD',
				separator: ' - ',
			}
		});

	detailPicker.on('apply.daterangepicker', function(ev, picker) {
		var start = picker.startDate.format(date_format),
			end = picker.endDate.format(date_format);

			$(this).closest('form').find('input[name="date_from"]').val( start );
			$(this).closest('form').find('input[name="date_to"]').val( end );

	});

	if ( detailPicker.length > 0 ) {
		detailPicker.data('daterangepicker').hide = function () {};
		detailPicker.data('daterangepicker').show();
	}

	// Mini-Cart Dropdown
	$('.header-cart-wrap .cart-icon').on('click', function(e) {
		$(this).toggleClass('active');
		$('.header-cart-wrap .mini-cart-container').toggleClass('show-dropdown');
	});

	$(document).on('click', 'body', function(e) {
		if ( $('.mini-cart-container.show-dropdown').length > 0 && ! $(e.target).is('.header-cart-wrap .cart-icon, .header-cart-wrap .cart-icon *, .mini-cart-container.show-dropdown, .mini-cart-container.show-dropdown *') ) {
			$('.header-cart-wrap .cart-icon').removeClass('active');
			$('.header-cart-wrap .mini-cart-container').removeClass('show-dropdown');
		}
	});

	// Guest Dropdown
	$('.guest-wrap .guest-value').on('click', function(e) {
		$('.guest-wrap .guest-dropdown-info').toggleClass('show-dropdown');
	});

	$(document).on('click', 'body', function(e) {
		if ( $('.guest-dropdown-info.show-dropdown').length > 0 && ! $(e.target).is('.guest-wrap .guest-value, .guest-wrap .guest-value *, .guest-dropdown-info.show-dropdown, .guest-dropdown-info.show-dropdown *') ) {
			$('.guest-wrap .guest-dropdown-info').removeClass('show-dropdown');
		}
	});

	// Guest Qty
	$('input.count-value').each(function() {
		var min = parseFloat( $(this).attr('min') );

		if ( min && min > 0 && parseFloat( $(this).val() ) < min ) {
			$(this).val(min);
		}
	});

	$(document).off('click', '.count-wrap .fa-minus, .count-wrap .fa-plus').on('click', '.count-wrap .fa-minus, .count-wrap .fa-plus', function(e) {
		/* Get values */
		var $qty = $(this).closest('.count-wrap').find('.count-value'),
			currentVal = parseFloat( $qty.val() ),
			min = parseFloat( $qty.attr('min') ),
			step = 1;

		/* Format Values */
		if ( ! currentVal || '' == currentVal ) {
			currentVal = 0;
		}

		if ( '' == min || 'NaN' == min ) {
			min = 0;
		}

		/* Change the Value */
		if ( $(this).hasClass('fa-plus') ) {
			$qty.val( currentVal + parseFloat(step) );
		} else {
			if ( min == currentVal || currentVal < min ) {
				$qty.val(min);
			} else {
				$qty.val( currentVal - parseFloat(step) );
			}
		}

		/* Trigger Change Event */
		$qty.trigger('change');
	});

	// Destination Carousel Slider
	$('.destination-carousel').each(function(index) {
		var $this = $(this),
			itemCol = $this.data('col');

		$this.owlCarousel({
			items: itemCol,
			loop: false,
			dots: false,
			nav: true,
			margin: 10,
			responsive: {
				1200: {
					items: itemCol
				},
				992: {
					items: 4
				},
				768: {
					items: 3,
					dots: true,
					nav: false,
				},
				460: {
					items: 2,
					dots: true,
					nav: false,
				},
				0: {
					items: 1,
					dots: true,
					nav: false,
				}
			}
		});
	});

	// Hotel Slider
	$('.package-carousel-inner .main-carousel-stage').owlCarousel({
		items: 5,
		loop: true,
		dots: true,
		nav: false,
		margin: 10,
		autoplay:true,
    	autoplayTimeout:5000,
    	autoplayHoverPause:true,
		responsive: {
			1600: {
				items: 5
			},
			1200: {
				items: 4
			},
			992: {
				items: 3
			},
			490: {
				items: 2
			},
			0: {
				items: 1
			}
		}
	});

	$('.single-travel-item .featured-imgs').each(function(index) {
		var $this = $(this);

		$this.slick({
			draggable: false,
			dots: true,
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
		});
	});

	$('a[data-toggle="tab"]').on('shown.bs.tab', function() {
		var sliderEle = $( $(this).attr('href') ).find('.featured-imgs');

		sliderEle.slick('destroy');

		sliderEle.slick({
			draggable: false,
			dots: true,
			infinite: false,
			slidesToShow: 1,
			slidesToScroll: 1,
		})
	}).first().trigger('shown.bs.tab');

	// Testimonial Carousel
	$('.testimonials-carousel .carousel-wrapper').each(function(index) {
		var $this = $(this),
			carouselCol = $this.data('col');

		$this.owlCarousel({
			items: carouselCol,
			loop: true,
			nav: false,
			dots: true,
			margin: 10,
			responsive: {
				1400: {
					items: 3
				},
				992: {
					items: 2
				},
				0: {
					items: 1
				}
			}
		});
	});

	// Wdiget Toggle
	$('.sidebar-section .widget-head').each(function(index) {
		var $this = $(this),
			widgetContent = $this.next('.widget-content');

		$this.on('click', function(e) {
			$this.toggleClass('collapsed-widget');
			widgetContent.toggleClass('content-collapsed');
			widgetContent.slideToggle(250);
		});
	});

	// price range
	if ( $("#price-range").length ) {
		var price_slide_min_val = 0;
		var price_slide_step = $("#price-range").data('slide-step');
		var price_slide_last_val = $("#price-range").data('slide-last-val');
		var price_slide_max_val = price_slide_last_val + price_slide_step;

		var def_currency = $("#price-range").data('def-currency');
		var min_price = $("#price-range").data('min-price');
		var max_price = $("#price-range").data('max-price');

		if (max_price == "no_max") { max_price = price_slide_max_val; }

		var url_noprice = $("#price-range").data('url-noprice').replace(/&amp;/g, '&');

		if ((min_price != 0) || (max_price != price_slide_max_val)) {
			$('#price-filter').collapse('show');
			$('a[href="#price-filter"]').removeClass('collapsed');
		}

		$("#price-range").slider({
			range: true,
			min: price_slide_min_val,
			max: price_slide_max_val,
			step: price_slide_step,
			values: [ min_price, max_price ],
			slide: function(event, ui) {
				// make handles uncollapse
				if ((ui.values[0] + 1) >= ui.values[1]) {
					return false;
				}

				// min price text
				$(".min-price-label").text(def_currency + ui.values[ 0 ]);

				// max price text
				max_price = ui.values[1];
				if (max_price == price_slide_max_val) {
					max_price = price_slide_last_val + '+';
				}
				$(".max-price-label").text(def_currency + max_price);
			},
			change: function(event, ui) {
				if (ui.values[0] != 0) {
					url_noprice += '&min_price=' + ui.values[0];
				}
				if (ui.values[1] != price_slide_max_val) {
					 url_noprice += '&max_price=' + ui.values[1];
				}
				if (url_noprice.indexOf("?") < 0) { url_noprice = url_noprice.replace(/&/, '?'); }
				window.location.href = url_noprice;
			}
		});

		$(".min-price-label").text(def_currency + $("#price-range").slider("values", 0));

		if ($("#price-range").slider("values", 1) == price_slide_max_val) {
			$(".max-price-label").text(def_currency + price_slide_last_val + "+");
		} else {
			$(".max-price-label").text(def_currency + $("#price-range").slider("values", 1));
		}
	}

	// Single Detail Photo Carousel
	$('#single-featured-carousel .featured-photo-carousel').owlCarousel({
		items: 1,
		loop: true,
		nav: true,
		dots: false,
		autoHeight: true
	});

	// Similar Details Carousel
	$('.similar-detail-slide .available-travel-package-wrap').owlCarousel({
		items: 4,
		loop: false,
		nav: true,
		dots: false,
		responsive: {
			1400: {
				dots: true,
				nav: false
			},
			1200: {
				items: 4,
				dots: true,
				nav: false
			},
			992: {
				items: 3,
				dots: true,
				nav: false
			},
			490: {
				items: 2,
				dots: true,
				nav: false
			},
			0: {
				items: 1,
				dots: true,
				nav: false
			}
		}
	});

	// Detail Photo Carousel
	$('.traveler-photos').owlCarousel({
		items: 4,
		loop: true,
		nav: true,
		dots: false,
		margin: 10,
		responsive: {
			992: {
				items: 4
			},
			490: {
				items: 3
			},
			0: {
				items: 2
			}
		}
	});

	// Single Collapse
	$('.single-collapse-wrap').each(function(index) {
		var $this = $(this),
			collapseLink = $this.find('.collapse-btn'),
			collapseContent = $this.find('.collapse-content');

		collapseLink.on('click', function(e) {
			e.preventDefault();

			collapseLink.toggleClass('lap-content');
			collapseContent.slideToggle(250);
		});
	});


	/* Accommodation Search Page */
	// accommodation type filter
	$("#accomodation-type-filter input[type='checkbox']").change(function(){
		var url_noacc_type = $("#accomodation-type-filter").data('url-noacc_type').replace(/&amp;/g, '&');

		if ($(this).val() == 'all') {
			$("#accomodation-type-filter input[type='checkbox']").prop( 'checked', false );
			$(this).prop( 'checked', true );
		} else {

			$("#accomodation-type-filter #all-type").prop( 'checked', false );

			$("#accomodation-type-filter input[type='checkbox']:checked").each(function(index){
				url_noacc_type += '&acc_type[]=' + $(this).val();
			});

		}

		if (url_noacc_type.indexOf("?") < 0) { url_noacc_type = url_noacc_type.replace(/&/, '?'); }

		window.location.href = url_noacc_type;
	});

	// accommodation rating filter
	$("#accomodation-rating-filter input[type='checkbox']").change(function(){
		var url_norating = $("#accomodation-rating-filter").data('url-norating').replace(/&amp;/g, '&');

		$("#accomodation-rating-filter input[type='checkbox']:checked").each(function(index){
			url_norating += '&rating[]=' + $(this).val();
		});


		if (url_norating.indexOf("?") < 0) { url_norating = url_norating.replace(/&/, '?'); }

		window.location.href = url_norating;
	});

	// amenity filter
	$("#amenities-filter input[type='checkbox']").change(function(){
		var url_noamenities = $("#amenities-filter").data('url-noamenities').replace(/&amp;/g, '&');

		$("#amenities-filter input[type='checkbox']:checked").each(function(index){
			url_noamenities += '&amenities[]=' + $(this).val();
		});

		if (url_noamenities.indexOf("?") < 0) { url_noamenities = url_noamenities.replace(/&/, '?'); }

		window.location.href = url_noamenities;

		return false;
	});

	// load more button click action on search result page
	$(".btn-load-more-accs").click(function(e){
		e.preventDefault();

		var url = $(this).attr('href');
		var _this = $(this);
		var wrapper = $(this).closest('.available-travel-package-wrap');
		var wrapper_class = $(this).closest('.available-travel-package-wrap').attr('class').split(" ").filter(Boolean).join('.');

		jQuery.ajax({
			url: url,
			type: "GET",
			success: function(response){
				var response_list =  $($.parseHTML(response)).find('.' + wrapper_class);
				wrapper.children('div').append(response_list.children('div').html());
				$(window).trigger('resize');
				var load_more_btn = response_list.find('.btn-load-more-accs');
				if (load_more_btn.length) {
					_this.attr('href', load_more_btn.attr('href'));
				} else {
					_this.remove();
				}
			}
		});

		return false;
	});

	// Blog thumb slide
	$('.photo-gallery-wrapper').each(function(e) {
		var carouselItem = $(this).find('.owl-carousel');

		carouselItem.owlCarousel({
			items: 1,
			loop: true,
			nav: true,
			dots: true,
			autoHeight: true
		});
	});

	// Sticky post share buttons
	if ( $('body').hasClass('single-post') ) {
		var topPos = $('.post-details-wrap').offset().top,
			elementHeight = $('.post-details-wrap').outerHeight(),
			shareBtnElement = $('.post-details-wrap .post-share-buttons'),
			shareBtnHeight = shareBtnElement.outerHeight(),
			maxTopVal = elementHeight - shareBtnHeight;

		$(window).scroll(function(e) {
			var scrollTop = $(this).scrollTop();

			if ( scrollTop > topPos - 100 ) {
				var topVal = scrollTop - topPos + 100;
				shareBtnElement.css( 'top', topVal );

				if ( scrollTop + shareBtnHeight > topPos + elementHeight ) {
					shareBtnElement.css( 'top', maxTopVal );
				}
			} else {
				shareBtnElement.css( 'top', 0 );
			}
		});
	}

	// Related posts slide
	$('.related-posts-section .owl-carousel').owlCarousel({
		items: 2,
		loop: false,
		nav: true,
		dots: false,
		autoHeight: true,
		margin: 30,
		responsive: {
			479: {
				items: 2,
				margin: 30
			},
			0: {
				items: 1,
				margin: 15
			}
		}
	});

	// load more button click action on search result page
    $("body").on('click', '.btn-add-wishlist', function(e) {
        e.preventDefault();

        var $this = $(this);
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'acc_add_to_wishlist',
                'accommodation_id' : $this.data('post-id')
            },
            success: function(response){
                if (response.success == 1) {
                    $this.addClass('btn-remove-wishlist');
                    $this.removeClass('btn-add-wishlist');
                } else {
                    alert(response.result);
                }
            }
        });
        return false;
    });

    // load more button click action on search result page
    $("body").on('click', '.btn-remove-wishlist', function(e) {
    	e.preventDefault();

    	var $this = $(this);
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'acc_add_to_wishlist',
                'accommodation_id' : $this.data('post-id'),
                'remove' : 1
            },
            success: function(response){
                if (response.success == 1) {
                    $this.addClass('btn-add-wishlist');
                    $this.removeClass('btn-remove-wishlist');
                } else {
                    alert(response.result);
                }
            }
        });
        return false;
    });

    // Dashboard form single datepicker
    $('.user-dashboard-wrap input[name="birthday"]').daterangepicker({
    	singleDatePicker: true,
    	locale: {
			format: date_format,//'YYYY-MM-DD',
			separator: ' - ',
		}
    });

    // change UI of file input
	$(".fileinput input[type=file]").each(function() {
		var obj = $(this);

		if (obj.parent().children(".custom-fileinput").length < 1) {
			obj.after('<input type="text" class="custom-fileinput" />');
			if (typeof obj.data("placeholder") != "undefined") {
				obj.next(".custom-fileinput").attr("placeholder", obj.data("placeholder"));
			}
			if (typeof obj.prop("class") != "undefined") {
				obj.next(".custom-fileinput").addClass(obj.prop("class"));
			}
			obj.parent().css("line-height", obj.outerHeight() + "px");
		}
	});

	$("body").on("change", ".fileinput input[type=file]", function() {
		var fileName = this.value,
			slashIndex = fileName.lastIndexOf("\\");

		if (slashIndex == -1) {
			slashIndex = fileName.lastIndexOf("/");
		}
		if (slashIndex != -1) {
			fileName = fileName.substring(slashIndex + 1);
		}

		$(this).next(".custom-fileinput").val(fileName);
	});

	// Ajax loading overlay
	$(document).ajaxStart(function(){
		$('.opacity-ajax-overlay').show();
	}).ajaxStop(function(){
		$('.opacity-ajax-overlay').hide();
	});

	// Close Popup
	$(document).bind('keydown', function (e) {
		var key = e.keyCode;

		if ($(".opacity-overlay:visible").length > 0 && key === 27) {
			e.preventDefault();
			$(".opacity-overlay").fadeOut();
		}
	});

	$(document).on("click touchend", ".opacity-overlay", function(e) {
		if (! $(e.target).is(".opacity-overlay .popup-content *")) {
			$(".opacity-overlay").fadeOut();
		}
	});

	// Modal popup
	$("body").on("click", ".soap-popupbox", function(e) {
		e.preventDefault();

		var sourceId = $(this).attr("href");

		if (typeof sourceId == "undefined") {
			sourceId = $(this).data("target");
		}
		if (typeof sourceId == "undefined") {
			return;
		}
		if ($(sourceId).length < 1) {
			return;
		}

		$(this).travPopup({
			wrapId: "soap-popupbox",
		});
	});

	// Update booking date calendar
	$('#change-date .datepicker-wrap input[type="text"]').each(function(e) {
		$(this).daterangepicker({
	    	singleDatePicker: true,
	    	locale: {
				format: date_format,//'YYYY-MM-DD',
				separator: ' - ',
			}
	    });
	});

	// Share button popup
	$('.single-detail-head .head-icon').on('click', function(e) {
		e.preventDefault();

		$('.single-detail-head .post-share-buttons').toggleClass('show');
	});

	$(document).on('click', 'body', function(e) {
		if ( $('.post-share-buttons.show').length > 0 && ! $(e.target).is('.single-detail-head .head-icon, .single-detail-head .head-icon *, .single-detail-head .post-share-buttons, .single-detail-head .post-share-buttons *') ) {
			$('.single-detail-head .post-share-buttons').removeClass('show');
		}
	});
});

/* Trav popup plugin */
(function($) {
	var stp, TravPopup = function(){};
	TravPopup.prototype = {
		constructor: TravPopup,
		init: function() {
			//
		},
		open: function(options, obj) {
			if (typeof options == "undefined") {
				options = {};
			}
			var wrapObj = options.wrapId ? "#" + options.wrapId : ".opacity-overlay";
			if ($(wrapObj).length < 1) {
				var idStr = options.wrapId ? " id='" + options.wrapId + "'" : "";
				$("<div class='opacity-overlay' " + idStr + "><div class='container'><div class='popup-wrapper'><i class='fa fa-spinner fa-spin spinner'></i><div class='col-xs-12 col-sm-9 popup-content'></div></div></div></div>").appendTo("body");
			}
			stp.wrap = $(wrapObj);
			stp.content = stp.wrap.find(".popup-content");
			stp.spinner = stp.wrap.find(".spinner");
			stp.contentContainer = stp.wrap.find(".popup-wrapper");

			if (stGlobals.isMobile) {
				stp.wrap.css({
					height: $(document).height(),
					position: 'absolute'
				});
				stp.contentContainer.css("top", $(window).scrollTop());
			}

			stp.updateSize();
			var sourceId = obj.attr("href");
			if (typeof sourceId == "undefined") {
				sourceId = obj.data("target");
			}

			if (options.type == "ajax") {
				stp.content.html('');
				stp.content.height('auto').css("visibility", "hidden");
				stp.wrap.fadeIn();
				stp.spinner.show();
				$("body").addClass("overlay-open");
				$.ajax({
					url: options.url,
					type: 'post',
					data: options.data,
					success: function(html) {
						stp.content.html("<a href=\"javascript:void(0);\" data-dismiss=\"modal\" style=\"text-align: right; display: block;\">X</a>" + html);
						if (options.callBack) {
							options.callBack(stp);
						}
						setTimeout(function() {
							stp.content.css("visibility", "visible");
							stp.spinner.hide();
						}, 100);
					}
				});
			} else if (options.type == "map") {
				stp.wrap.fadeIn();
				stp.spinner.show();
				var lngltd = options.lngltd.split(",");
				var contentWidth = stp.content.width();
				stp.content.gmap3({
					clear: {
						name: "marker",
						last: true
					}
				});
				var zoom = options.zoom ? parseInt(options.zoom, 10) : 12;
				stp.content.height(contentWidth * 0.5).gmap3({
					map: {
						options: {
							center: lngltd,
							zoom: zoom
						}
					},
					marker: {
						values: [
							{latLng: lngltd}

						],
						options: {
							draggable: false
						},
					}
				});
				$("body").addClass("overlay-open");
			} else {
				stp.content.children().hide();
				if (stp.content.children(sourceId).length > 0) {
					;// ignore
				} else {
					$(sourceId).appendTo(stp.content);
				}
				$(sourceId).show();
				stp.spinner.hide();
				stp.wrap.fadeIn(function() {
					//$(sourceId).find(".input-text").eq(0).focus();
					$("body").addClass("overlay-open");
				});
			}
		},
		close: function() {
			$("body").removeClass("overlay-open");
			$("html").css("overflow", "");
			$("html").css("margin-right", "");
			stp.spinner.hide();
			stp.wrap.fadeOut();
		},
		updateSize: function() {
			if (stGlobals.isIOS) {
				var zoomLevel = document.documentElement.clientWidth / window.innerWidth;
				var height = window.innerHeight * zoomLevel;
				stp.contentContainer.css('height', height);
			} else if (stGlobals.isMobile) {
				stp.contentContainer.css('height', $(window).height());
			}
		},
		getScrollbarSize: function() {
			if (document.body.scrollHeight <= $(window).height()) {
				return 0;
			}
			if(stp.scrollbarSize === undefined) {
				var scrollDiv = document.createElement("div");
				scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
				document.body.appendChild(scrollDiv);
				stp.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
				document.body.removeChild(scrollDiv);
			}
			return stp.scrollbarSize;
		}
	}

	$.fn.travPopup = function(options) {
		stp = new TravPopup();
		stp.init();

		$(document).bind('keydown', function (e) {
			var key = e.keyCode;
			if ($(".opacity-overlay:visible").length > 0 && key === 27) {
				e.preventDefault();
				stp.close();
			}
		});

		$(document).on("click touchend", ".opacity-overlay", function(e) {
			if ( !$(e.target).is(".opacity-overlay .popup-content *")) {
				e.preventDefault();
				stp.close();
			}
		});

		$(window).resize(function() {
			stp.updateSize();
		});

		stp.open(options, $(this));

		$(document).on("click touchend", ".opacity-overlay [data-dismiss='modal']", function(e) {
            stp.close();
        });
		return $(this);
	};


  // By mirai

  // hide all child age inputs
  $('.child-age-inputs-container').hide();

  // Show child age inputs when changing kids number
  $('body').on('change', 'input[name=kids]', mt_show_child_age_inputs);

})(jQuery);



// Function to show correct number of child age inputs in all forms on page
function mt_show_child_age_inputs(){
  var $ = jQuery.noConflict();
  $('input[name=kids]').each(function(){
    var curr_kids = $(this).val();
    var form = $(this).closest("form");

    if(curr_kids > 0){
      form.find('.child-age-inputs-container').show();
    } else {
      form.find('.child-age-inputs-container').hide();
    }

    while( form.find('.single-child-age').length > curr_kids && form.find('.single-child-age').length > 1 ){
      form.find('.single-child-age').last().remove();
    }

    while( form.find('.single-child-age').length < curr_kids ){
      var clone = form.find('.single-child-age').last().clone();
      var number_obj = clone.find(".child-input-number");
      var my_index = parseInt(number_obj.html()) + 1;
      number_obj.html( my_index );
      form.find('.child-age-inputs-container').append(clone);
    }
  });
}
