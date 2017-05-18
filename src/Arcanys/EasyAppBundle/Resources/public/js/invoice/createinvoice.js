$(function() {
    var d           = new Date();
    var month       = d.getMonth() + 1;
    var day         = d.getDate();
    var output      = (month < 10 ? '' : '') + month + '/' + (day < 10 ? '0' : '') + day + '/' + d.getFullYear();
    $('.date-issued .modified').html(output);
    $('.invoicedate, .date-due-input').val(output);

    spNumbersOnly('.cur-ammount');
    alphaNumeric('.invoiceamount');
    numbersOnly('input[name="acctnum"]');

    $(".vendor-select, .chartofaccounts-select").select2();
    $(".vendor-select2").select2({placeholder: "Vendor"});
    $(".invoicedate").datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function(dateStr, inst) {
            var v = $('.vendordate').val(),
                p = parseInt(v),
                o = $.datepicker.parseDate('mm/dd/yy', dateStr);
            if (v != 'any' || v.length == 0) {
                o.setDate(o.getDate('mm/dd/yy') + p);
                $('.date-due-input').val(o.toLocaleDateString());
                console.log(o.toLocaleDateString());
            }
        },
        onClose: function() {
            $(".date-due-input").focus();
        }
    });

    $('.date-due-input').datepicker({
        dateFormat: 'mm/dd/yy',
        onClose: function() {
            $(".manager-select").focus();
        }
    });

    jQuery(".form-invoice").validate({
        ignore: '',
        rules: {
            'entity': {
                required: true
            },
            'vendor': {
                required: true
            },
            'amount': {
                required: true
            },
            'checknumber': {
                required: true
            },
            'invoiceamount': {
                required: true
            },
            'managerapproval': {
                required: true
            },
            'chartofaccounts': {
                required: true
            }
        }
    });

    chkInvoiceAmt();
    invoiceCreate(link);

});

function vendorSelect(url) {
    $(document.body).on('change', '.vendor-select', function() {
        var getValue    = $(this).val(),
            data        = { id: getValue };
        $.ajax({
            url: url, type: 'POST', data: data,
            beforeSend: function() { $('.vendor-details').addClass('loader'); },
            success: function(response) {
                $('.vendor-details').removeClass('loader');
                $('.vendor-details').fadeIn(300, function() {
                    $(this).show();
                    $('.vendor-details .vendorname span').html(response.name);
                    $('.vendor-details .vendoraddress span').html(response.address);
                    $('.vendordate').val(response.form);

                    // display if vendor has no W9 file
                    if ( response.info == 1 ) {
                        $('.display-status').html(response.wform).css('background-color', '#c1272d').show();
                    } else {
                        $('.display-status').html('').hide();
                    }

                    // select chart of accounts related to vendor
                    if ( response.charts ) {
                        $('.chartofaccounts-select').select2( "val", response.charts );
                    }
                });
            }
        });
    });
}

function chkInvoiceAmt() {
    $('.cur-ammount').on('keypress', function() {
        var getData     = $(this).val(),
            $this       = $(this),
            extraamt    = '';

        window.setTimeout(function() {
            if ( $this.val().substr(getData.indexOf('.')) ) {
                extraamt = $this.val().substr(getData.indexOf('.')) + '/100 Dollars';
                if ( $this.val().substr(getData.indexOf('.')) ) {
                    extraamt = extraamt.replace('.00', '');
                    extraamt = extraamt.replace('.', 'and ');
                }
                if ( $this.val().substr(getData.indexOf('.')) == '.00' || $this.val().substr(getData.indexOf('.')) == '0' ) {
                    extraamt = extraamt.replace('/100', '');
                }
                if ( $this.val().substr(getData.indexOf('.')).length <= 1 ) {
                    extraamt = ' Dollars';
                }
                $('.cents-amt').html(' ' + extraamt);
            }
        }, 0);
    });
}

function validateInvoiceDup(url) {
    $('.cur-ammount').on('blur', function() {
        var getData     = $(this).val(),
            $this       = $(this),
            data        = { amount: getData, vendor: $('.vendor-select').val() },
            extraamt    = '';

        window.setTimeout(function() {
            if ( $this.val().substr(getData.indexOf('.')) ) {
                extraamt = $this.val().substr(getData.indexOf('.')) + '/100 Dollars';
                if ( $this.val().substr(getData.indexOf('.')) ) {
                    extraamt = extraamt.replace('.00', '');
                    extraamt = extraamt.replace('.', 'and ');
                }
                if ( $this.val().substr(getData.indexOf('.')) == '.00' || $this.val().substr(getData.indexOf('.')) == '0' ) {
                    extraamt = extraamt.replace('/100', '');
                }
                if ( $this.val().substr(getData.indexOf('.')).length <= 1 ) {
                    extraamt = ' Dollars';
                }
                $('.cents-amt').html(' ' + extraamt);
            }
        }, 0);

        if (getData) {
            $.ajax({
                type: 'POST', data: data, url: url,
                beforeSend: function() { $('.amt-validate').html('validating...').show(); },
                success: function(datas) {
                    $('.amt-validate').hide();
                    if (datas.info == 1) {
                        $('.display-status').html(datas.message).css('background-color', '#c1272d').show();
                    } else if (datas.info == 0) {
                        $('.display-status').html('').hide();
                    }
                }
            });
        } else if (getData == '') {
            $('.email-check').html();
        }
    });
}

function invoiceAmt(url) {
    $('.invoiceamount').on('blur', function() {
        var $this   = $(this),
            getData = $(this).val();

        window.setTimeout(function() {
            var data    = { entity: $('.entity-select').val(),
                amount: $('.cur-ammount').val(),
                vendor: $('.vendor-select').val(),
                invoicenum: $this.val()
            };
            if ($this.val().length) {
                $.ajax({
                    type: 'POST', data: data, url: url,
                    beforeSend: function() { $('.invoiceamount-status').html('validating...').show(); },
                    success: function(datas) {
                        if (datas.info == 1) {
                            $('.display-status').html(datas.message).css('background-color', '#c1272d').show();
                        } else if (datas.info == 0) {
                            $('.display-status').html('').hide();
                        }
                    }
                });
            } else if (getData == '') {
                $('.email-check').html();
            }
        }, 0);
    });
}

function invoiceCreate(link) {
    $('.form-invoice').on('submit', function(e) {
        var url     = $(this).attr('action'),
            data    = {
                entity: $('.entity-select').val(),
                checknum: $('input.checknumber').val(),
                datedue: $('.date-due-input').val(),
                vendor: $(".vendor-select").select2("val"),
                amount: $('.cur-ammount').val(),
                invoiceamount: $('.invoiceamount').val(),
                invoicedate: $('.invoicedate').val(),
                managerapproval: $('.manager-select').val(),
                description: $('.description').val(),
                chartofaccounts: $('.chartofaccounts-select').select2("val"),
                comments: $('.comment-txbox').val(),
                token: $('.token').val()
            };
        if ( $('.entity-select').val().length == '' ) {
            return false;
        } else if ( $('.date-due-input').val().length == '' ) {
            return false;
        } else if ( $(".vendor-select").select2("val") == '' ) {
            return false;
        } else if ( $('.cur-ammount').val().length == '' ) {
            return false;
        } else if ( $('.manager-select').val().length == '' ) {
            return false;
        } else if ( $('.chartofaccounts-select').select2("val") == '' ) {
            return false;
        } else if ( $('.invoiceamount').val().length == '' ) {
            return false;
        } else if ( $('.invoicedate').val().length == '' || $('.invoicedate').val() == '' ) {
            return false;
        } else {
            $.ajax({
                type: 'POST', url: url, data: data,
                beforeSend: function() {
                    $(this).find(':submit').attr('disabled','disabled');
                    $('.submitforapproval').val('Submitting....');
                },
                success: function(datas) {
                    console.log('test10');
                    $('.submitforapproval').val('Submit for Approval');
                    showNotification({
                        message: "A new Invoice has been added!",
                        type: "success",
                        autoClose: true,
                        duration: 5
                    });

                    setTimeout(function() {
                        $(".vendor-select").select2('val', '');
                        $('textarea, select, input[type="text"]').val('');
                        $('.error-message').hide();
                        $('.error-message .red').html('');
                        $('.vendor-details span').html('');
                        window.location.href=link;
                    },2500);
                }
            });
        }
        e.preventDefault();
    });
}