$(function() {
    // MASTER REGISTRY
    $('.month-to-date').hide();
    $( "#from" ).datepicker({
        defaultDate: "+1w",
        changeMonth: false,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: false,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
            $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });

    $('input[type="button"]').on('click', function(e) {
        $('.month-to-date').removeClass('display');
        $('.month-to-date').hide();
        $(this).closest('form').find('input[type="text"]').val("");
        e.preventDefault();
    });

    $('.dateTodate').on('click', function(e) {
        $('.dateTodate .month').addClass('selected');
        $('.getMonth .year').removeClass('selected');
        $('.getYear .year').removeClass('selected');
        $('.month-to-date').toggleClass('display');
        e.preventDefault();
    });
	
	$('.headermain').on('click', function(e) {
		var rel = $(this).attr('rel');
		$("input[type='checkbox'][name='header["+rel+"]']").click();
	});
	
	$('#export_button').on('click', function(e) {
		$('#export_form').submit();
	});
});

function getMonthValue(url) {
    $('.getMonth').on('click', function(e) {
        var totalE  = $('.total-expense'),
            totalR  = $('.total-revenue'),
            totalC  = $('.total-capital'),
            balance = $('.total-balance');
        $('.getMonth .year').addClass('selected');
        $('.getYear .year').removeClass('selected');
        $('.dateTodate .month').removeClass('selected');
        $('.month-to-date').removeClass('display');
        $('.month-to-date').hide();
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            beforeSend: function() { 
				totalE.html('calculating amount....'); 
				totalR.html('calculating amount....');
				totalC.html('calculating amount....');
				balance.html('calculating amount....');
			},
			success: function(datas) {
				var balanceval = 0;
				if (datas.total) {
					balanceval = balanceval - datas.total;
					totalE.html('$ ' + datas.total).digits();
					totalE.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalE.html('Value is empty');
				}
				
				if (datas.rev_total) {
					balanceval = balanceval + datas.rev_total;
					totalR.html('$ ' + datas.rev_total);
					totalR.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalR.html('Value is empty');
				}
				
				if (datas.cap_total) {
					balanceval = balanceval + datas.cap_total;
					totalC.html('$ ' + datas.cap_total);
					totalC.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalC.html('Value is empty');
				}
				
				if (balanceval == 0) {
					balance.html('Value is empty');
				}
			}
        });
        e.preventDefault();
    });
}

function getYearValue(url) {
    $('.getYear').on('click', function(e) {
        var totalE  = $('.total-expense'),
            totalR  = $('.total-revenue'),
            totalC  = $('.total-capital'),
            balance = $('.total-balance');
        $('.getYear .year').addClass('selected');
        $('.getMonth .year').removeClass('selected');
        $('.dateTodate .month').removeClass('selected');
        $('.month-to-date').removeClass('display');
        $('.month-to-date').hide();
        $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            beforeSend: function() { 
				totalE.html('calculating amount....'); 
				totalR.html('calculating amount....');
				totalC.html('calculating amount....');
				balance.html('calculating amount....');
			},
			success: function(datas) {
				var balanceval = 0;
				if (datas.total) {
					balanceval = balanceval - datas.total;
					totalE.html('$ ' + datas.total).digits();
					totalE.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalE.html('Value is empty');
				}
				
				if (datas.rev_total) {
					balanceval = balanceval + datas.rev_total;
					totalR.html('$ ' + datas.rev_total);
					totalR.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalR.html('Value is empty');
				}
				
				if (datas.cap_total) {
					balanceval = balanceval + datas.cap_total;
					totalC.html('$ ' + datas.cap_total);
					totalC.digits();
					balance.html('$ ' + balanceval).digits();
					balance.digits();
				} else {
					totalC.html('Value is empty');
				}
				
				if (balanceval == 0) {
					balance.html('Value is empty');
				}
			}
        });
        e.preventDefault();
    });
}

function getMonthToMonthValue() {
    $('.month-go').on('submit', function(e) {
        var url     = $(this).attr('action'),
            data    = { from: $( "#from" ).val(), to: $( "#to" ).val() },
            total   = $('.total-expense'),
            balance = $('.total-balance');
		
		var totalR  = $('.total-revenue'),
            totalC  = $('.total-capital');

        if ( $( "#from").val() == '' ) {
            $( "#from").focus();
            return false;
        } else if ( $( "#to").val() == '' ) {
            $( "#to").focus();
            return false;
        } else {
            $.ajax({
                type: "POST", url: url, data: data, dataType: 'json',
                beforeSend: function() { 
					total.html('calculating amount....'); 
					totalR.html('calculating amount....');
					totalC.html('calculating amount....');
					balance.html('calculating amount....');
				},
                success: function(datas) {
					var balanceval = 0;
                    if (datas.total) {
						balanceval = balanceval - datas.total;
                        total.html('$ ' + datas.total).digits();
                        total.digits();
						balance.html('$ ' + balanceval).digits();
                        balance.digits();
                    } else {
                        total.html('Value is empty');
                    }
					
					if (datas.rev_total) {
						balanceval = balanceval + datas.rev_total;
						totalR.html('$ ' + datas.rev_total);
						totalR.digits();
						balance.html('$ ' + balanceval).digits();
                        balance.digits();
					} else {
						totalR.html('Value is empty');
					}
					
					if (datas.cap_total) {
						balanceval = balanceval + datas.cap_total;
						totalC.html('$ ' + datas.cap_total);
						totalC.digits();
						balance.html('$ ' + balanceval).digits();
                        balance.digits();
					} else {
						totalC.html('Value is empty');
					}
					
					if (balanceval == 0) {
						balance.html('Value is empty');
					}
                }
            });
        }

        $('#from, #to').datepicker( "option", "maxDate", null );
        $('#from, #to').datepicker( "option", "minDate", null );
        $('.month-to-date').removeClass('display');
        $('.month-to-date').hide();
        $(this).closest('form').find('input[type="text"]').val("");
        e.preventDefault();
    });
}