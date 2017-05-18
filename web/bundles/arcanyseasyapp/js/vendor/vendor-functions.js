$(function() {
    numbersOnly('input[name="phone"]');
    numbersOnly('input[name="zip"]');
    numbersOnly('input[name="acctnumber"]');
    numbersOnly('input[name="local"]');
    vendorAddress('input[name="address"]');
    lettersOnly('input[name="city"]');
    lettersOnly('input[name="state"]');
    specialCharAlphaNumeric2('input[name="name"]');
    specialCharAlphaNumeric2('input[name="contact"]');
    $(".textPhone, .txPhoneList").text(function(i, text) {
        text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
        return text;
    });
    $(".chartofaccounts-select").select2();

    jQuery(".form-vendor, .update-form-vendor").validate({
        ignore: '',
        rules: {
            'name': {
                required: true
            },
            'address': {
                required: true
            },
            'city': {
                required: true
            },
            'state': {
                required: true
            },
            'zip': {
                required: true
            }
        },
        messages: {
            chartofaccounts: {
                required: 'This field is required.'
            }
        }
    });

    updateVendor();
});

function addVendor(link) {
    $('.form-vendor').on('submit', function(e) {
        var url     = $(this).attr('action'),
            data    = $(this).serializeObject();

        if ( $('input[name="phone"]').val().length <= 9 ) {
            $('.textPhoneerror').html('This field should be exactly 10 digits').show();
            setTimeout(function() {
                $('.textPhoneerror').fadeOut(function() {
                    $(this).hide();
                });
            }, 4000);
            return false;
        }
        else if ( $('input[name="email"]').val() === '' || !IsEmail($('input[name="email"]').val()) ) {
            return false;
        }
        else if ($('.email-check').html() == 'Email already exist!') {
            return false;
        }
        else {
            $('.vendor-btn').val('Saving...');
            $.post(url, data, function(datas) {
                $('.vendor-btn').val('Save');

                showNotification({
                    message: "A new Vendor has been added!",
                    type: "success",
                    autoClose: true,
                    duration: 5
                });

                var view  = link,
                    view  = view.replace('idnumber', datas.id);
                setTimeout(function() {
                    window.location=view;
                }, 2000);
            }, 'json');
        }

        $('input[type="text"]').val();
        $('textarea').val();
        $('.textPhoneerror').hide();
        e.preventDefault();
    });
}

function updateVendor() {
    $(document.body).on('click', '.edit-user', function(e) {
        var getID = $(this).attr('id');

        $('.fields span').hide();
        $('.fields .user-textfield, .fields .user-button, .fields .chartofaccounts-select span.select2-chosen').show();
        $('.select2-container, .chart-accounts-option').show();
        $('input[type="checkbox"]').removeAttr('disabled');
        e.preventDefault();
    });

    $(document.body).on('submit', '.update-form-vendor', function(e) {
        var url         = $(this).attr('action'),
            getID       = $(this).data('id'),
            data        = $(this).serializeObject();

        if ( $('input[name="phone"]').val().length <= 9 ) {
            $('.textPhoneerror').html('This field should be exactly 10 digits').show();
            setTimeout(function() {
                $('.textPhoneerror').fadeOut(function() {
                    $(this).hide();
                });
            }, 4000);
            return false;
        } else if ( $('input[name="email"]').val() === '' || !IsEmail($('input[name="email"]').val()) ) {
            return false;
        } else if ($('.email-check').html() == 'Email already exist!') {
            return false;
        } else {
            $.post(url, data, function(response) {
                showNotification({
                    message: "This vendor has been updated.",
                    type: "success",
                    autoClose: true,
                    duration: 5
                });
                $('.textChartsofaccounts').html( response.charts );
            });
            var comments = $('textarea').val(),
                commentdata = 0,
                payterms = $('select[name="paymentterms"]').val(),
                payvalue = '';

            if (comments.length == 0) {
                commentdata = 'No Comment';
            } else {
                commentdata = comments;
            }

            switch(payterms) {
                case '1': payvalue = 'Due upon receipt'; break;
                case '2': payvalue = 'Net 10'; break;
                case '3': payvalue = 'Net 15'; break;
                case '4': payvalue = 'Net 30'; break;
                case '5': payvalue = 'Net 45'; break;
            }

            $('.fields span').show();
            $('.fields .user-textfield, .fields .user-button').hide();
            $('.select2-container, .chart-accounts-option').hide();
            $('.textPhoneerror').hide();
            $('input[type="checkbox"]').attr('disabled', true);

            $('.textName').html( $('input[name="name"]').val() );
            $('.textAddress').html( $('input[name="address"]').val() );
            $('.textCity').html( $('input[name="city"]').val() );
            $('.textState').html( $('select[name="state"]').val() );
            $('.textZip').html( $('input[name="zip"]').val() );
            $('.textContact').html( $('input[name="contact"]').val() ).text(function(i, text) {
                text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
                return text;
            });
            $('.textPhone').html( $('input[name="phone"]').val() ).text(function(i, text) {
                text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
                return text;
            });
            $('.textLocalNum').html( $('input[name="local"]').val() );
            $('.textEmail').html( $('input[name="email"]').val() );
            $('.textComments').html( commentdata );
            $('.textPaymentTerms').html( payvalue );
            $('.textAcctnum').html( $('input[name="acctnumber"]').val() );
            $('.list-vendor tr#' + getID + ' td:first-child').html( $('input[name="name"]').val() );
            $('.list-vendor tr#' + getID + ' td:last-child').html( $('input[name="phone"]').val() );
            $('.list-vendor tr#' + getID + ' td:last-child').text(function(i, text) {
                text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
                return text;
            });
        }
        e.preventDefault();
    });
}

function displayData(url, url2) {
    $('.list-vendor tr').on('click', function(e) {
        var getID = $(this).attr('id'),
            view  = url2,
            view  = view.replace('idnumber', getID),
            data  = { id: getID },
            load  = $('.display-loader'),
            right = $('.account-info');

        if ( history.pushState )
            window.history.pushState({}, document.title, view);

        $(this).toggleClass('selected').siblings().removeClass('selected');

        load.addClass('loader');
        $.post(url, data, function(data) {
            if (load.hasClass('loader')) {
                load.removeClass('loader');
            }
            right.html(data);
        });
    });
}