$(function() {
    var string          = '',
        currentamt      = $('.currentamt').html(),
        extraamt        = 0;

    if ( $('.checknum').length ) {
        var getbankaccount  = $('.checknum').html(),
            renewNum        = (getbankaccount.length - 4)+1;

        for ( var i = 0; i < renewNum; i++) {
            string = getbankaccount.charAt(i);
            string = string.replace(string, '.');
        }
        $('.checknum').html('...' + getbankaccount.slice(-4));
    }

    spNumbersOnly('.cur-ammount');

    // AMOUNT TO WORDS
    if ( currentamt.substr(currentamt.indexOf('.')) == '.00' ) {
        extraamt = 'Dollars';
    } else {
        extraamt = ' and ' + currentamt.substr(currentamt.indexOf('.')) + '/100 Dollars';
        extraamt = extraamt.replace('.', '');
    }
    $('.numbertowords').html(toWords(currentamt) + extraamt);

    $('.cur-ammount').on('keypress', function() {
        var getData     = $(this).val(),
            $this       = $(this);

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

    // SLIDESHOW PLUGIN
    $("#invoice-thumbnails").owlCarousel({
        navigation : true,
        slideSpeed : 300,
        paginationSpeed : 400,
        singleItem : true,
        autoHeight : true,
        items : 1,
        lazyLoad : true
    });

    // SOME PLUGINS
    $('.display-message').digits();
    $(".info-popup").colorbox({ inline:true, width:"50%" });
    $('.zoooom').colorbox({ rel:'group3', transition:"fade" });
    $('.datepicker').datepicker({
        autoclose: true,
        startDate: new Date()
    });

    // COMMENTS
    $('.comment-btn').on('blur', function() {
        $('.comment-notification').fadeOut(300, function() {
            $(this).html('');
        });
    });
});

function updateInvoicenotApproved(url, id, link) {
    $('.updatenotapp-btn').on('click', function() {
        var data = { id: id };
        $(this).val('Updating...');
        $.post(url, data, function(datas) {
            $(this).val('Update Invoice');
            window.location.href = link;
        });
    });
}

function approveBtn(url, getID) {
    var statusmsg = $('.display-status');

    $('.approve').on('click', function(e) {
        var role  = $(this).data('user'),
            stat  = $(this).data('status'),
            data  = { id: getID, status: stat, roleuser: role };

        statusmsg.fadeIn(300, function() {
            $(this).html('processing...');
            $(this).show();
        });

        $("html, body").animate({ scrollTop: 0 }, "medium");
        $.post(url, data, function(datas) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            if (datas.status == 'Approved') {
                statusmsg.addClass('approvedmsg');
                $('.approve').addClass('approved');
                statusmsg.html('Approved');
                $('.display-conditions').hide();
            }
        });

        statusmsg.removeClass('partialmsg');
        statusmsg.removeClass('notapprovedmsg');
        $('.partial').removeClass('partialp');
        $('.notapprove').removeClass('notapproved');
        $('.content-display').removeClass('notapproved-content');
        $('.invoice-btn.blue').addClass('has-selected');

        e.preventDefault();
    });
}

function partialBtn(url, getID) {
    var statusmsg = $('.display-status');

    $('.partial').on('click', function(e) {
        var role  = $(this).data('user'),
            stat  = 5,
            data  = '';

        /*if ( role == 0 ) {
            stat = 5;
        }*/
        statusmsg.fadeIn(300, function() { $(this).html('processing...').show(); });

        $('.cur-ammount').show();
        $('.currentamt').hide();

        $("html, body").animate({ scrollTop: 0 }, "medium");
        data = { id: getID, status: stat, roleuser: role }
        $.post(url, data, function(datas) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            if (datas.status == 'Partial Payment') {
                statusmsg.addClass('partialmsg');
                $('.partial').addClass('partialp');
                statusmsg.html('Partial Payment');
                $('.display-conditions').show();
                $('.comment-txbox').focus();
                $('.display-conditions a').html('Partial Payment');
            }
            $('.price.view').addClass('price-view');
            $('.invoice-btn.blue').attr('data-status', stat);
            $('.comment-notification').attr('data-info', '1'); // if user is not approved
        });

        statusmsg.removeClass('approvedmsg');
        $('.approve').removeClass('approved');
        statusmsg.removeClass('notapprovedmsg');
        $('.notapprove').removeClass('notapproved');
        $('.content-display').removeClass('notapproved-content');
        $('.invoice-btn.blue').addClass('has-selected');

        e.preventDefault();
    });
}

function notApproveBtn(url, getID) {
    var statusmsg = $('.display-status');

    $('.notapprove').on('click', function(e) {
        var role  = $(this).data('user'),
            stat  = $(this).data('status'),
            data  = { id: getID, status: stat, roleuser: role };

        statusmsg.fadeIn(300, function() { $(this).html('processing...').show(); });
        $("html, body").animate({ scrollTop: 0 }, "medium");
        $.post(url, data, function(datas) {
            $("html, body").animate({ scrollTop: 0 }, "slow");
            if (datas.status == 'Not Approved') {
                statusmsg.addClass('notapprovedmsg');
                $('.notapprove').addClass('notapproved');
                $('.content-display').addClass('notapproved-content');
                statusmsg.html('Not Approved');
                $('.display-conditions').show();
                $('.display-conditions a').html('Not Approved');
                $('.notapprove').attr('data-info', '1'); // navigate to know if data is not approved
                $('.comment-notification').attr('data-info', '1'); // if user is not approved

                if ( role == 1 ) {
                    $('.invoice-btn.blue').attr('data-status', '22');
                } else {
                    $('.invoice-btn.blue').attr('data-status', '2');
                }

            }
        });
        statusmsg.removeClass('approvedmsg');
        $('.approve').removeClass('approved');
        statusmsg.removeClass('partialmsg');
        $('.partial').removeClass('partialp');
        $('.invoice-btn.blue').addClass('has-selected');

        e.preventDefault();
    });
}

function addcommentinvoice(url, userid, homeLink) {
    var commentgetData = $('.comment-txbox').val(),
        approvedBtn    = $('.invoice-btn.approve'),
        commentinvId   = $('.invoice-btn.blue').data('id'),
        commentroles   = $('.invoice-btn.blue').data('role'),
        commentname    = $('.invoice-btn.blue').data('name'),
        commentdata    = { data: commentgetData, id: commentinvId, userid: userid };

    if (commentgetData == '') {
        if ( approvedBtn.hasClass('approved') == false ) {
            $('.comment-notification').fadeIn(300, function() {
                $(this).html('Comment is required').show();
            });
            return false;
        }
    } else if (commentgetData.length <= 10) {
        $('.comment-notification').fadeIn(300, function() {
            $(this).html('Comment must be atleast more than 10 characters.').show();
        });
        return false;
    } else {
        $.ajax({
            url: url, type: 'POST', data: commentdata,
            beforeSend: function() {
                $('.comment-btn').val('posting...');
                $('.comment-btn').attr('disabled', true);
            },
            success: function() {
                $('.comment-btn').val('post comment');
                $('.comment-notification').removeAttr('data-info'); // if user has added a comment
                var setrole = 0, styles = 0, data = 0;
                if (commentroles == 'ROLE_SUPER_ADMIN' || commentroles == 'ROLE_ADMIN') {
                    setrole  = 'Admin';
                    styles   = 'admin';
                    data     = commentgetData;
                } else if (commentroles == 'ROLE_ACCOUNTANT') {
                    setrole  = '[Acct]';
                    styles   = 'acct';
                    data     = commentgetData;
                } else {
                    setrole  = '[Mgr]';
                    styles   = 'mgr';
                    data     = commentgetData;
                }
                $('.comment-list li:first-child').after(
                    '<li><strong><label class="' + styles + '">' + setrole + ' ' + commentname + ':</label></strong> ' + data + '</li>'
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
            }
        });
    }
}

function procesStatus(url, data, homeLink) {
    var commentgetData = $('.comment-txbox').val(),
        approvedBtn    = $('.invoice-btn.approve'),
        btn  = $('.invoice-btn.blue'),
        stat = btn.data('status');

    if ( stat == 3 || stat == 33 ) {
        ajaxProcess(url, data, homeLink);
    } else {
        if ( commentgetData == '' ) {
            if ( approvedBtn.hasClass('approved') == false ) {
                $('.comment-notification').fadeIn(300, function() {
                    $(this).html('Comment is required').show();
                });
                return false;
            } else {
                ajaxProcess(url, data, homeLink);
            }
        } else if ( commentgetData.length <= 10 ) {
            if ( approvedBtn.hasClass('approved') == false ) {
                $('.comment-notification').fadeIn(300, function() {
                    $(this).html('Comment must be atleast more than 10 characters.').show();
                });
                return false;
            } else {
                ajaxProcess(url, data, homeLink);
            }
        } else {
            ajaxProcess(url, data, homeLink);
        }
    }
}

function ajaxProcess(url, data, homeLink) {
    $.ajax({
        type: 'POST', url: url, data: data,
        beforeSend: function() { $('.invoice-btn.blue').val('please wait..'); },
        success: function(data) {
            $('.invoice-btn.blue').val('Finish');
            if (data.info == 4) {
                $('.amt-validate').html(data.msg).show();
                $('.cur-ammount').focus();
                setTimeout(function() {
                    $('.amt-validate').fadeOut(300, function() {
                        $(this).html('').hide();
                    });
                }, 2000);
            }
            else {
                window.location.href = homeLink;
            }
        }
    });
}

function selectBankInfoUpdate(url, homeLink, cancelLink) {
    $('.pendingbank-btn').on('click', function(e) {
        var getID       = $(this).data('id'),
            newoption   = $('.newoption').val(),
            checknum    = $('.check-number-view').html(),
            currentamt  = parseFloat($('.account-invoice').html().replace(/[$,]/g, '')),
            invoiceamt  = parseFloat($('.currentamt').html().replace(/,|$/g, ''));

        if ( newoption.length == '' || newoption.length == 0 ) {
            $('.newoption').focus();
            return false;
        }

        else if ( currentamt < invoiceamt ) {
            jQuery( "#dialog-confirm" ).dialog({
                resizable: false,
                height: 215,
                width: 320,
                modal: true,
                buttons: {
                    Cancel: function() { window.location.href = cancelLink; },
                    "Proceed Anyway": function() {
                        $.ajax({
                            type: 'POST', url: url, data: { id: getID, value: newoption, checknum: checknum },
                            beforeSend: function() { $('.pendingbank-btn').val('Updating...'); },
                            success: function() {
                                $('.pendingbank-btn').val('Update');
                                window.location.href = homeLink;
                            }
                        });
                    }
                }
            });
            return false;
        }

        else {
            $.ajax({
                type: 'POST', url: url, data: { id: getID, value: newoption, checknum: checknum },
                beforeSend: function() { $('.pendingbank-btn').val('Updating...'); },
                success: function() {
                    $('.pendingbank-btn').val('Update');
                    window.location.href = homeLink;
                }
            });
        }
        e.preventDefault();
    });
}