$(function() {
	$( "#revenue_dateadded" ).datepicker({
		dateFormat: 'mm-dd-yy',
		minDate: new Date()
	}).datepicker('setDate', '0');
	
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

    if ( $('.checknum').length ) {
        validateBankAcct('.checknum');
    }
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
            },
            success: function(datas) {
                if (datas.total) {
                    totalE.html('$ ' + datas.total);
                    balance.html('$ ' + datas.total);
                    totalE.digits();
                    balance.digits();
                } else {
                    totalE.html('Value is empty');
                    balance.html('Value is empty');
                }
				
				if (datas.rev_total) {
                    totalR.html('$ ' + datas.rev_total);
                    totalR.digits();
                } else {
                    totalR.html('Value is empty');
                }
				
				if (datas.cap_total) {
                    totalC.html('$ ' + datas.cap_total);
                    totalC.digits();
                } else {
                    totalC.html('Value is empty');
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
            },
            success: function(datas) {
                if (datas.total) {
                    totalE.html('$ ' + datas.total);
                    balance.html('$ ' + datas.total);
                    totalE.digits();
                    balance.digits();
                } else {
                    totalE.html('Value is empty');
                    balance.html('Value is empty');
                }
				
				if (datas.rev_total) {
                    totalR.html('$ ' + datas.rev_total);
                    totalR.digits();
                } else {
                    totalR.html('Value is empty');
                }
				
				if (datas.cap_total) {
                    totalC.html('$ ' + datas.cap_total);
                    totalC.digits();
                } else {
                    totalC.html('Value is empty');
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
				},
                success: function(datas) {
                    if (datas.total) {
                        total.html('$ ' + datas.total).digits();
                        balance.html('$ ' + datas.total).digits();
                        total.digits();
                        balance.digits();
                    } else {
                        total.html('Value is empty');
                        balance.html('Value is empty');
                    }
					
					if (datas.rev_total) {
						totalR.html('$ ' + datas.rev_total);
						totalR.digits();
					} else {
						totalR.html('Value is empty');
					}
					
					if (datas.cap_total) {
						totalC.html('$ ' + datas.cap_total);
						totalC.digits();
					} else {
						totalC.html('Value is empty');
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

function getBalance(id, list) {
	$(document.body).on('change', '#' + id, function() {
		$('#bal_for_' + id).remove();
		$('#details_for_' + id).remove();
		$('[for="'+ id +'"]').after('<span id="bal_for_'+ id +'" rel="'+ list[$(this).val()]['cur_bal'] +'" style="float: right; padding-right: 17px; font-size: 11px;">current balance: $ '+ numberWithCommas(list[$(this).val()]['cur_bal']) +'</span>');
		$('#'+ id).after('<span id="details_for_'+ id +'" rel="'+ list[$(this).val()]['bank_name'] +' ...'+ list[$(this).val()]['bank_account_no'] +'" style="padding-right: 17px; font-size: 11px;">'+ list[$(this).val()]['bank_name'] +'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...'+ list[$(this).val()]['bank_account_no'] +'</span>');
	});
}

function getBalanceOnLoad(id, list) {
	$('[for="'+ id +'"]').after('<span id="bal_for_'+ id +'" rel="'+ list[$('#'+ id).val()]['cur_bal'] +'" style="float: right; padding-right: 17px; font-size: 11px;">current balance: $ '+ numberWithCommas(list[$('#'+ id).val()]['cur_bal']) +'</span>');
	$('#'+ id).after('<span id="details_for_'+ id +'" rel="'+ list[$('#'+ id).val()]['bank_name'] +' ...'+ list[$('#'+ id).val()]['bank_account_no'] +'" style="padding-right: 17px; font-size: 11px;">'+ list[$('#'+ id).val()]['bank_name'] +'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...'+ list[$('#'+ id).val()]['bank_account_no'] +'</span>');
}

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

function notEqualFrom(field){
    if(field.val() == $('#revenue_entity_from').val()){
        return "* To and From Entities must be different";
    }
}

function notEqualTo(field){
    if(field.val() == $('#revenue_entity_to').val()){
        return "* To and From Entities must be different";
    }
}

function notEqualFromEdit(field){
    if(field.val() == $('#revenue_entityIdFrom').val()){
        return "* To and From Entities must be different";
    }
}

function notEqualToEdit(field){
    if(field.val() == $('#revenue_entityIdTo').val()){
        return "* To and From Entities must be different";
    }
}


function displayListofEntityinfo(classname, url, displayinfo) {
    $('.' + classname).on('change', function(e) {
        var value = $(this).val();

        $.ajax({
            type: 'POST', url: url, data: { value: value },
            beforeSend: function() { $('.' + displayinfo).addClass('loader').show(); },
            success: function(data) {
                $('.' + displayinfo).removeClass('loader');
                $('.' + displayinfo).show();
                $('.' + displayinfo).html(data.info).show();
            }
        });

        e.preventDefault();
    });
}