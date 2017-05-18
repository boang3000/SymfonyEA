$(function() {
    displayAddchart('.add-icon');
    displayModifychart('.modify-icon');
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
                console.log(dateStr);
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

    if ( $('.checknum').length ) {
        validateBankAcct('.checknum');
    }

    // FILTER AMOUNT TO WORDS
    var currentamt      = $('.cur-ammount').val(),
        extraamt        = 0;

    if ( currentamt.substr(currentamt.indexOf('.')) == '.00' ) {
        extraamt = 'Dollars';
    } else {
        extraamt = ' and ' + currentamt.substr(currentamt.indexOf('.')) + '/100 Dollars';
        extraamt = extraamt.replace('.', '');
    }
    $('.numbertowords').html(toWords(currentamt) + extraamt);

    // SLIDESHOW PLUGIN
    $("#invoice-thumbnails").owlCarousel({
        navigation : true,
        pagination: true,
        slideSpeed : 300,
        paginationSpeed : 400,
        singleItem : true,
        autoHeight : true,
        items : 1,
        lazyLoad : true
    });

    $(".display-thumb li a.img-display").click(function(e){
        $(this).addClass("selected")
            .siblings()
            .removeClass("selected");

        var img = $(this).children().clone().addClass( "animated fadeInDown" );
        $("div#img").html(img);
        e.preventDefault();
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

    // CONVERT AMOUNT TO WORDS WHILE TYPING WEEE
    $('.cur-ammount').on('keypress', function() {
        var getData = $(this).val(),
            $this   = $(this);

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
});

function updateFormInvoice() {
    $('.form-invoice').on('submit', function(e) {
        var url     = $(this).attr('action'),
            getId   = $('.comment-btn').data('id'),
            roles   = $('.comment-btn').data('role'),
            name    = $('.comment-btn').data('name'),
            comment = $('.comment-txbox').val(),
            data    = {
                entity: $('.entity-select').val(),
                checknum: $('input.checknumber').val(),
                datedue: $('.date-due-input').val(),
                vendor: $(".vendor-select").select2("val"),
                amount: $('.cur-ammount').val(),
                invoiceamount: $('.invoiceamount').val(),
                invoicedate: $('.invoicedate').val(),
                managerapproval: $('.manager-select').val(),
                chartofaccounts: $('.chartofaccounts-select').select2("val"),
                description: $('.description').val(),
                comments: comment
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
        } else if ( $('.invoicedate').val().length == '' ) {
            return false;
        } else if (comment.length <= 10) {
            $('.comment-notification').fadeIn(300, function() {
                $(this).html('Comment must be atleast more than 10 characters.');
            });
            return false;
        } else {
            $(this).find(':submit').attr('disabled','disabled');
            $.ajax({
                type: 'POST', url: url, data: data,
                beforeSend: function() { $('.submitforapproval').val('Submitting....'); },
                success: function(datas) {
                    $('.submitforapproval').val('Submit for Approval');
                    showNotification({
                        message: "Invoice number " + $('.invoiceamount').val() + " has been updated!",
                        type: "success",
                        autoClose: true,
                        duration: 5
                    });

                    var setrole = 0, styles = 0, data = 0, username = 0;
                    if (roles == 'ROLE_SUPER_ADMIN' || roles == 'ROLE_ADMIN') {
                        setrole  = 'Admin';
                        styles   = 'admin';
                        data     = comment;
                    } else if (roles == 'ROLE_ACCOUNTANT') {
                        setrole  = '[Acct]';
                        styles   = 'acct';
                        data     = comment;
                    } else {
                        setrole  = '[Mgr]';
                        styles   = 'mgr';
                        data     = comment;
                    }
                    $('.comment-list li:first-child').after(
                        '<li><strong><label class="' + styles + '">' + setrole + ' ' + name + ':</label></strong> ' + data + '</li>'
                    );

                    $('.comment-txbox').val('');
                }
            });
        }
        $(this).find(':submit').removeAttr('disabled');
        $('#file_upload').uploadify('upload', '*');
        e.preventDefault();
    });
}

function addComment(url, id) {
    $('.comment-btn').on('click', function() {
        var getData = $('.comment-txbox').val(),
            getId   = $(this).data('id'),
            roles   = $(this).data('role'),
            name    = $(this).data('name'),
            data    = { data: getData, id: getId, userid: id };

        if (getData == '') {
            $('.comment-notification').fadeIn(300, function() {
                $(this).html('Comment is required');
            });
            return false;
        } else if (getData.length <= 10) {
            $('.comment-notification').fadeIn(300, function() {
                $(this).html('Comment must be atleast more than 10 characters.');
            });
            return false;
        } else {
            $.ajax({
                url: url, type: 'POST', data: data,
                beforeSend: function() { $('.comment-btn').attr('disabled', true); $('.comment-btn').val('posting...'); },
                success: function() {
                    $('.comment-btn').val('post comment');
                    $('.comment-notification').removeAttr('data-info'); // if user has added a comment
                    var setrole = 0, styles = 0, data = 0, username = 0;
                    if (roles == 'ROLE_SUPER_ADMIN' || roles == 'ROLE_ADMIN') {
                        setrole  = 'Admin';
                        styles   = 'admin';
                        data     = getData;
                    } else if (roles == 'ROLE_ACCOUNTANT') {
                        setrole  = '[Acct]';
                        styles   = 'acct';
                        data     = getData;
                    } else {
                        setrole  = '[Mgr]';
                        styles   = 'mgr';
                        data     = getData;
                    }
                    $('.comment-list li:first-child').after(
                        '<li><strong><label class="' + styles + '">' + setrole + ' ' + name + ':</label></strong> ' + data + '</li>'
                    );
                    showNotification({
                        message: "Your comment has been posted!",
                        type: "success",
                        autoClose: true,
                        duration: 5
                    });
                    $('.comment-txbox').val('');
                    $('.comment-notification').fadeOut(300, function() {
                        $(this).html('');
                    });
                    $('.display-conditions').fadeOut(300, function() {
                        $(this).hide();
                    });
                    $('.comment-btn').attr('disabled', false);
                    $('.comment-btn').attr('disabled', false);
                }
            });
        }
    });
}

function venderSelect(url) {
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
                });
            }
        });
    });
}

function entitySelect(url) {
    $(document.body).on('change', '.entity-select', function() {
        var getValue    = $(this).val(),
            data        = { id: getValue };
        $.ajax({
            url: url, type: 'POST', data: data,
            beforeSend: function() {
                $('.error-message').fadeIn(300,function() {
                    $(this).show();
                    $('.display-message').html('searching...').show();
                });
            },
            success: function(datas) {
                $('.display-message').hide();
                $('.bank-name-details').html(datas.bankname);
            }
        });
    });
}

function currentAmt(url) {
    $('.cur-ammount').on('blur', function() {
        var getData  = $(this).val(),
            $this    = $(this),
            extraamt = '',
            data     = { amount: getData, vendor: $('.vendor-select').val() };

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
                beforeSend: function() { /*$('.amt-validate').html('validating...').show();*/ },
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

function checkInvoiceAmt(url) {
    $('.invoiceamount').on('blur', function() {
        var $this   = $(this);

        window.setTimeout(function() {
            if ($this.val().length) {
                var data    = { entity: $('.entity-select').val(),
                    amount: $('.cur-ammount').val(),
                    vendor: $('.vendor-select').val(),
                    invoicenum: $this.val()
                };
                $.ajax({
                    type: 'POST', data: data, url: url,
                    beforeSend: function() { $('.invoiceamount-status').html('validating...').show(); },
                    success: function(datas) {
                        if (datas.info == 1) {
                            $('.invoiceamount-status').hide();
                            $('.display-status').html(datas.message).show().css('background-color', '#c1272d');
                        } else if (datas.info == 0) {
                            $('.display-status').html('').hide();
                            $('.invoiceamount-status').hide();
                        }
                        console.log(datas);
                    }
                });
            } else if ($this.val().length == 0) {
                $('.email-check').html();
            }
        }, 0);
    });
}