$(function() {
    numbersOnly('.textBankPhoneNum');
    numbersOnly('.textBankAccountNum');
    numbersOnly('.textBankRoutingNum');
    numbersOnly('.textStartCheckNum');
    numbersOnly('.textZipcode');
    numbersDotOnly('.textPresentBalance');
    spNumbersOnly('input[name="curbalance"]');
    specialCharAlphaNumeric2('input[name="entityname"]');
    specialCharAlphaNumeric2('.textBank');
    specialCharAlphaNumeric2('.textBankContact');

    jQuery(".form-entity").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname[]': {
                required: true
            },
            'bankcontact[]': {
                required: true
            },
            'bankphonenum[]': {
                required: true
            },
            'bankemail[]': {
                required: true,
                email: true
            },
            'bankaddress[]': {
                required: true
            },
            'city[]': {
                required: true,
                alpha: true
            },
            'state[]': {
                required: true
            },
            'zipcode[]': {
                required: true
            },
            'bankaccount[]': {
                required: true
            },
            'bankroute[]': {
                required: true
            },
            'startbalance[]': {
                required: true
            },
            'curbalance[]': {
                required: true
            }
        }
    });

    jQuery("#bank-1").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname': {
                required: true
            },
            'bankcontact': {
                required: true
            },
            'bankphonenum': {
                required: true
            },
            'bankemail': {
                required: true,
                email: true
            },
            'bankaddress': {
                required: true
            },
            'city': {
                required: true,
                alpha: true
            },
            'state': {
                required: true
            },
            'zipcode': {
                required: true
            },
            'bankaccount': {
                required: true
            },
            'bankroute': {
                required: true
            },
            'startbalance': {
                required: true
            },
            'curbalance': {
                required: true
            }
        }
    });

    jQuery("#bank-2").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname': {
                required: true
            },
            'bankcontact': {
                required: true
            },
            'bankphonenum': {
                required: true
            },
            'bankemail': {
                required: true,
                email: true
            },
            'bankaddress': {
                required: true
            },
            'city': {
                required: true,
                alpha: true
            },
            'state': {
                required: true
            },
            'zipcode': {
                required: true
            },
            'bankaccount': {
                required: true
            },
            'bankroute': {
                required: true
            },
            'startbalance': {
                required: true
            },
            'curbalance': {
                required: true
            }
        }
    });

    jQuery("#bank-3").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname': {
                required: true
            },
            'bankcontact': {
                required: true
            },
            'bankphonenum': {
                required: true
            },
            'bankemail': {
                required: true,
                email: true
            },
            'bankaddress': {
                required: true
            },
            'city': {
                required: true,
                alpha: true
            },
            'state': {
                required: true
            },
            'zipcode': {
                required: true
            },
            'bankaccount': {
                required: true
            },
            'bankroute': {
                required: true
            },
            'startbalance': {
                required: true
            },
            'curbalance': {
                required: true
            }
        }
    });

    jQuery("#bank-4").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname': {
                required: true
            },
            'bankcontact': {
                required: true
            },
            'bankphonenum': {
                required: true
            },
            'bankemail': {
                required: true,
                email: true
            },
            'bankaddress': {
                required: true
            },
            'city': {
                required: true,
                alpha: true
            },
            'state': {
                required: true
            },
            'zipcode': {
                required: true
            },
            'bankaccount': {
                required: true
            },
            'bankroute': {
                required: true
            },
            'startbalance': {
                required: true
            },
            'curbalance': {
                required: true
            }
        }
    });

    jQuery("#bank-5").validate({
        rules: {
            'entityname': {
                required: true
            },
            'bankname': {
                required: true
            },
            'bankcontact': {
                required: true
            },
            'bankphonenum': {
                required: true
            },
            'bankemail': {
                required: true,
                email: true
            },
            'bankaddress': {
                required: true
            },
            'city': {
                required: true,
                alpha: true
            },
            'state': {
                required: true
            },
            'zipcode': {
                required: true
            },
            'bankaccount': {
                required: true
            },
            'bankroute': {
                required: true
            },
            'startbalance': {
                required: true
            },
            'curbalance': {
                required: true
            }
        }
    });

    $.validator.addMethod("alpha", function(value, element) {
        return this.optional(element) || value == value.match(/^[a-zA-Z\s]+$/);
    }, "Invalid city");

    $(document.body).on('click', '.tabentity-edit', function(e) {
        var getID = $(this).data('id');
        $('.tabentity-title-' + getID).hide();
        $('.submit-tabentity-btn-' + getID).show();
        $('.exit-tabentity-btn-' + getID).show();
        $('.tabentity-text-' + getID).show();
        e.preventDefault();
    });

    $(document.body).on('click', '.exit-tabentity-btn', function(e) {
        var getID = $(this).data('id');
        $('.tabentity-title-' + getID).show();
        $('.submit-tabentity-btn-' + getID).hide();
        $('.exit-tabentity-btn-' + getID).hide();
        $('.tabentity-text-' + getID).hide();
        e.preventDefault();
    });

    $(document.body).on('click', '.submit-tabentity-btn', function(e) {
        var tabID    = $(this).data('id'),
            getID    = $(this).data('value'),
            tabvalue = $('.tabentity-text').val(),
            data     = { id: getID, value: tabvalue };

        if ( tabvalue == '' ) {
            return false;
        } else {
            $.ajax({
                type: 'POST', url: tablink, data: data,
                beforeSend: function() { },
                success: function() {
                    $('.tabentity-title-' + tabID).html(tabvalue);
                    $('.tabentity-title-' + tabID).show();
                    $('.submit-tabentity-btn-' + tabID).hide();
                    $('.exit-tabentity-btn-' + tabID).hide();
                    $('.tabentity-text-' + tabID).hide();
                }
            });
        }
        e.preventDefault();
    });

    $(".bankphonenum").text(function(i, text) {
        text = text.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
        return text;
    });

    updateEntity();

    removeSpace('textBank');
    removeSpace('textBankContact');
    removeSpace('textBankPhoneNum');
    removeSpace('textBankEmail');
    removeSpace('textBankAddress');
    removeSpace('textCity');
    removeSpace('textState');
    removeSpace('textZipcode');
    removeSpace('textBankAccountNum');
    removeSpace('textBankRoutingNum');
    removeSpace('textStartCheckNum');
    removeSpace('textPresentBalance');
    removeSpace('textComments');
});

jQuery.validator.addMethod('minStrict', function (value, el, param) {
    return value > param;
}, jQuery.validator.format("Please enter the correct value for {0} + {1}"));

function removeSpace(classname) {
    $('.update-entity-form .' + classname).each(function(k, v) {
        $(this).val($.trim($(this).val()));
    });
}

function createNewEntity(link) {
    $('.form-entity').on('submit', function(e) {
        var url     = $(this).attr('action'),
            data    = $(this).serializeObject();

        if ( $('input[name="entityname"]').val() === '' ) {
            return false;
        }
        else if ( Number($('#textPresentBalance').val()) > Number('99999999.99') ) {
            notify('');
            return false;
        }
        else if ( Number($('#textPresentBalance2').val()) > Number('99999999.99') ) {
            notify(2);
            return false;
        }
        else if ( Number($('#textPresentBalance3').val()) > Number('99999999.99') ) {
            notify(3);
            return false;
        }
        else if ( Number($('#textPresentBalance4').val()) > Number('99999999.99') ) {
            notify(4);
            return false;
        }
        else if ( Number($('#textPresentBalance5').val()) > Number('99999999.99') ) {
            notify(5);
            return false;
        }
        else {
            $('.entity-btn').val('Saving...');
            $(this).find(':submit').attr('disabled','disabled');
            $.post(url, data, function(datas) {
                $('.entity-btn').val('Save');
                showNotification({
                    message: "A new Entity has been added!",
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
        e.preventDefault();
    });
}

function createNewTabEntity(url) {
    var data    = $('.form-entity').serializeObject();

    $.post(url, data, function(datas) {
        console.log(datas);
    }, 'json');
}

function getBankName(url) {
    for (i = 1; i <= 5; i++) {
        var j = (i == 1 ? '' : i);
        $('#textBank' + j).on('keyup', function(e) {
            var getData = $(this).val();
            if ( getData > 2 ) {
                $.ajax({
                    url: url, type: 'POST',
                    data: { data: getData },
                    beforeSend: function() {
                        $('#textBank' + j).addClass('loader');
                    },
                    success: function(data) {
                        $('#textBank' + j).removeClass('loader');
                        console.log(data);
                    }
                });
            }
            console.log(getData);
            e.preventDefault();
        });
    }
}

function addTab(title, url) {
    if ($('#tt').tabs('exists', title)){
        $('#tt').tabs('select', title);
    } else {
        var content = $('#' + url).html();
        $('#tt').tabs('add',{
            title:title,
            content:content,
            closable:true
        });
    }
}

function updateEntity() {
    $( ".entity-btn" ).click(function(e) {
        e.preventDefault();

        // Validation
        if ($('input[name="entityname"]').val() === '') {
            $('.entityname-error').html('Entity Name is required.').show();
            setTimeout(function() {
                $('.entityname-error').fadeOut(function() {
                    $(this).hide();
                });
            }, 6000);
            return;
        }

        for (i = 1; i <= 5; i++) {
            $("#bank-" + i).validate();
            $("#bank-" + i).valid();

            var j = (i == 1 ? '' : i);
            if (Number($('#textPresentBalance' + j).val()) > Number('99999999.99')) {
                notify(j);
                return;
            }
        }

        var bank = [];
        for (i = 1; i <= 5; i++) {
            bank.push($('#bank-' + i).serializeObject());
        }

        var me = $(this);
        me.val('Saving...').attr('disabled','disabled');
        $.ajax({
            type: 'POST',
            url: $('#formId').attr('action'),
            data: {
                textName: $('#textName').val(),
                input: JSON.stringify(bank)
            },
            success: function(data, textStatus, jQxhr) {
                $('.list-entity tr#' + $('#formId').data('id') + ' td:first-child').html($('#textName').val());

                me.val('Save').removeAttr('disabled');

                showNotification({
                    message: (data.success ? 'This Entity has been updated.' : 'An error has occurred.'),
                    type: 'success',
                    autoClose: true,
                    duration: 5
                });
                //location.reload();
            },
            error: function(jqXhr, textStatus, errorThrown) {
            }
        });
    });
}

function notify(key) {
    if ( key == '' ) {
        key = '';
    }
    $('.entityname-error').html('One of the tabs has exceeded a maximum balance of $ 99,999,999.00').show();
    setTimeout(function() {
        $('.entityname-error').fadeOut(function() {
            $(this).hide();
        });
    }, 6000);

    $('.curbalance-error' + key).html('You have exceed a maximum balance of $ 99,999,999.00').show();
    setTimeout(function() {
        $('.curbalance-error' + key).fadeOut(function() {
            $(this).hide();
        });
    }, 4000);
}
