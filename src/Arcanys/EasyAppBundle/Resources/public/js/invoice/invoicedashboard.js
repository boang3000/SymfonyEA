$(function() {
    $(".invoice-new").colorbox({ inline:true, width:"50%" });
    $(".more-comments").colorbox({ iframe:true, width:"45%", height:"60%" });

    var bankaccount     = $('.checknum'),
        getCurrentURL   = window.location.pathname;

    bankaccount.each(function() {
        var getbankaccount  = $(this).html(),
            renewNum        = (getbankaccount.length - 4)+1,
            string          = '';

        for ( var i = 0; i < renewNum; i++) {
            string = getbankaccount.charAt(i);
            string = string.replace(string, '.');
        }
        $(this).html('...' + getbankaccount.slice(-4));
    });

    setTimeout(function() {
        $('#list-' + getCurrentURL.slice(32)).fadeOut(function() {
            $(this).remove();
        });
    }, 2500);

    $('.multiple-invoice').on('click', function(e) {
        if ($('.create-multiform').is(":hidden")) {
            $('.create-multiform').slideDown('slow', function () {
                $('.create-multiform').show();
            });
        } else {
            $('.create-multiform').slideUp('slow');
        }
        e.preventDefault();
    });
});

function createMultiform(url) {
    numbersOnly('input[name="q"]');
    $(document.body).on('click', '.form-multiple-btn', function(e) {
        var num  = $('.form-multiple input[name="q"]').val(),
            Nurl = url.replace('number', num);

        if ( num.length == 0 || num.length == '' || num == '0' ) {
            return false;
        }
        else if ( num <= 1 ) {
            $('.multiform-text').html('There is a minimum of 2 and maximum of 5 invoices limit').show();
            setTimeout(function() {
                $('.multiform-text').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }
        else if ( num >= 6 ) {
            $('.multiform-text').html('Maximum of 5 invoices only').show();
            setTimeout(function() {
                $('.multiform-text').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        } else {
            window.location.href=Nurl;
        }
        e.preventDefault();
    });
}

function selectEntity(url) {
    $('.manager-select').on('change', function() {
        var getID = $(this).val();
        if ( getID.length == '' ) {
            return false;
        } else {
            $.ajax({
                url: url, type: 'POST', data: { id: getID },
                beforeSend: function() { $('.display-loader').addClass('loader') },
                success: function(data) {
                    $('.display-loader').removeClass('loader');
                    $('.display-manager-invoices').html(data);
                }
            });
        }
    });
}