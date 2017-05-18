$(function() {
    var d           = new Date();
    var month       = d.getMonth() + 1;
    var day         = d.getDate();
    var output      = (month < 10 ? '0' : '') + month + '/' + (day < 10 ? '0' : '') + day + '/' + d.getFullYear();
    $('.date-issued .modified').html(output);
    $('.invoicedate, .date-due-input').val(output);

    amountToWords();
    spNumbersOnly('.cur-ammount');
    alphaNumeric('.invoiceamount');
    numbersOnly('input[name="q"]');
    numbersOnly('input[name="acctnum"]');

    $(".vendor-select, .chartofaccounts-select").select2();
    $(".vendor-select, .chartofaccounts-select").select2('val', '');
    $('.token').val(generateUUID());
    $(".invoicedate").datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function(dateStr, inst) {
            var v = $('.vendordate').val(),
                p = parseInt(v),
                o = $.datepicker.parseDate('mm/dd/yy', dateStr);
            if (v != 'any' || v == '') {
                o.setDate(o.getDate('mm/dd/yy') + p);
                $('.date-due-input').val(o.toLocaleDateString());
                console.log(dateStr);
            }
        },
        onClose: function() {
            $(".date-due-input").focus();
        }
    });

    $('.date-due-input').datepicker({
        onClose: function() {
            $(".manager-select").focus();
        }
    });

    $(document.body).on('click', '.pagination .next', function(e) {
        jQuery(".form-invoice").validate({
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
                'invoiceamount': {
                    required: true
                },
                'invoicedate': {
                    required: true
                },
                'datedue': {
                    required: true
                },
                'managerapproval': {
                    required: true
                },
                'description': {
                    required: true
                },
                'chartofaccounts': {
                    required: true
                }
            }
        });
        e.preventDefault();
    });

    validateForm();
});

function validateForm() {
    jQuery(".form-invoice").validate({
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
            'invoiceamount': {
                required: true
            },
            'invoicedate': {
                required: true
            },
            'datedue': {
                required: true
            },
            'managerapproval': {
                required: true
            },
            'description': {
                required: true
            },
            'chartofaccounts': {
                required: true
            }
        }
    });
}

function amountToWords() {
    $(document.body).on('keypress', '.cur-ammount', function() {
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

function amountToWords2() {
    $(document.body).on('blur', '.cur-ammount', function() {
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
                $('.cents-amt').html(toWords(getData) + ' ' + extraamt).show();
            }
        }, 0);
    });
}

function displayAmountToWords() {
    var getData     = $('.cur-ammount').val(),
        $this       = $('.cur-ammount'),
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
            $('.cents-amt').html(toWords(getData) + ' ' + extraamt).show();
        }
    }, 0);
}

function fadeMessage(){
    console.log('save');
}

function saveinvoice(url) {
    // AUTOSAVE
    $("input,select,textarea").autosave({
        url: url,
        method: "post",
        grouped: true,
        success: function(data) {
            console.log('success');
            setTimeout('fadeMessage()',1500);
        },
        send: function(){
            console.log('Sending data....');
        },
        dataType: "html"
    });
}

function protectBankacct(bankacct) {
    var bankaccount     = $(bankacct),
        getbankaccount  = bankaccount.html(),
        renewNum        = (getbankaccount.length - 4)+1,
        string          = '';
    for ( var i = 0; i < renewNum; i++) {
        string = getbankaccount.charAt(i);
        string = string.replace(string, '.');
    }
    bankaccount.html('...' + getbankaccount.slice(-4));
}

function invoiceValidate(url) {
    $(document.body).on('blur', '.invoiceamount', function() {
        var $this   = $(this),
            getData = $(this).val();

        window.setTimeout(function() {
            var data = { entity: $('.entity-select').val(),
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
                            $('.display-status').html(datas.message).show().css('background-color', '#c1272d');
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

function amountValidation(url) {
    $(document.body).on('blur', '.cur-ammount', function() {
        var getData     = $(this).val(),
            $this       = $(this),
            data        = { amount: getData, vendor: $('.vendor-select').val() },
            extraamt    = '';

        toWords($('.cur-ammount').val());
        window.setTimeout(function() {
            toWords($(this).val());
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
                    $('.amt-validate').html('').hide();
                    if (datas.info == 1) {
                        $('.display-status').html(datas.message).css('background-color', '#c1272d').show();
                    } else if (datas.info == 0) {
                        $('.display-status').html('').hide();
                    }
                    toWords($('.cur-ammount').val());
                }
            });
        } else if (getData == '') {
            $('.email-check').html();
        }
    });
}

function entityVendorSelection(vendorurl) {
    $(document.body).on('change', '.vendor-select', function() {
        var getValue    = $(this).val(),
            data        = { id: getValue };
        $.ajax({
            url: vendorurl, type: 'POST', data: data,
            beforeSend: function() {
                $('.vendor-details').addClass('loader').show();
            },
            success: function(response) {
                $('.vendor-details').removeClass('loader');
                $('.vendor-details').fadeIn(300, function() {
                    $(this).show();
                    $('.vendor-details .vendorname span').html(response.name).show();
                    $('.vendor-details .vendoraddress span').html(response.address).show();
                    $('.vendordate').val(response.form);
                });
            }
        });
    });
}