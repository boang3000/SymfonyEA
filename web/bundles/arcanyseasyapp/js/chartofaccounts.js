function displayAddchart(selector) {
    $(document.body).on('click', selector, function(e) {
        $('.chartaccounts-form-add').stop().animate({ width: 'toggle' }, 350);
        $('.chartaccounts-form-modify').hide();
        e.preventDefault();
    });
}

function displayModifychart(selector) {
    $(document.body).on('click', selector, function(e) {
        $('.chartaccounts-form-modify').stop().animate({ width: 'toggle' }, 350);
        $('.chartaccounts-form-add').hide();
        e.preventDefault();
    });
}

// ADDING
function addChartdata(selector, url) {
    numbersOnly('input[name="acctnum"]');
    $(document.body).on('click', selector, function(e) {
        var addchart = $('input[name="addchart"]').val(),
            acctnum  = $('input[name="acctnum"]').val(),
            acctname = $('input[name="acctname"]').val(),
            info     = $('.empty-data').data('info'),
            data     = { data: addchart, acctnum: acctnum, acctname: acctname };

        if ( addchart.length == '' ) {
            $('.chart-add-info').html('This field is required').show();
            setTimeout(function() {
                $('.chart-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else if ( acctnum.length == '' ) {
            $('.acctnum-add-info').html('This field is required').show();
            setTimeout(function() {
                $('.acctnum-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else if ( acctname.length == '' ) {
            $('.acctname-add-info').html('This field is required').show();
            setTimeout(function() {
                $('.acctname-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else {
            $.ajax({
                url: url, type: 'POST', data: data,
                beforeSend: function() { $(selector).val('Adding...'); },
                success: function(getdata) {
                    $(selector).val('Add');
                    $('input[name="addchart"]').val('');
                    $('input[name="acctnum"]').val('');
                    $('input[name="acctname"]').val('');

                    if ( $('.empty-data').length && info == 1 ) {
                        $('.empty-data').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }

                    $('.chartofaccounts-select').append('<option value="' + getdata.id + '">' + acctnum + ' ' + acctname + ' ' + addchart + '</option>');
                    $('.modify-chart-list ul').append(
                        '<li data-id="' + getdata.id + '" id="chart-parent-' + getdata.id + '">' +
                            '<label class="chartname-' + getdata.id + '">' + addchart + '</label>' +
                            '<div class="edit-field-charts">' +
                                '<input type="text" name="editchart" class="chartaccounts-input" value="' + addchart + '" data-id="' + getdata.id + '" />' +
                                '<span class="charts-info chart-edit-info" data-id="' + getdata.id + '"></span>' +
                                '<input type="text" name="acctnum" class="chartaccounts-input" value="' + acctnum + '" data-id="' + getdata.id + '" />' +
                                '<span class="charts-info acctnum-edit-info" data-id="' + getdata.id + '"></span>' +
                                '<input type="text" name="acctname" class="chartaccounts-input" value="' + acctname + '" data-id="' + getdata.id + '" />' +
                                '<span class="charts-info acctname-edit-info" data-id="' + getdata.id + '"></span>' +

                                '<input type="button" class="chartaccounts-button charts-edit-btn" data-id="' + getdata.id + '" value="Edit" />' +
                                '<input type="button" class="chartaccounts-button charts-cancel-btn" data-id="' + getdata.id + '" value="Cancel" />' +
                            '</div>' +
                            '<a href="#" class="modify-chart-btn" data-id="' + getdata.id + '"><i class="chart-btn-icon modify"></i></a>' +
                            '<a href="#" class="delete-chart-btn" data-id="' + getdata.id + '"><i class="chart-btn-icon delete"></i></a>' +
                        '</li>'
                    );
                    $('.chart-add-info').html(addchart + ' has been added').show();
                    setTimeout(function() {
                        $('.chart-add-info').fadeOut(300, function() {
                            $(this).hide();
                        });
                    }, 3500);
                }
            });
        }
        e.preventDefault();
    });
}

// MODIFY
function modifyChartdata(url) {
    numbersOnly('input[name="acctnum"]');
    $(document.body).on('click', '.modify-chart-btn', function(e) {
        var getID = $(this).data('id');
        $('#chart-parent-' + getID + ' .edit-field-charts').show();
        $('#chart-parent-' + getID + ' label.chartname-' + getID).hide();
        $('#chart-parent-' + getID + ' .modify-chart-btn').hide();
        $('#chart-parent-' + getID + ' .delete-chart-btn').hide();
        e.preventDefault();
    });

    $(document.body).on('click', '.charts-cancel-btn', function(e) {
        var getID = $(this).data('id');
        $('#chart-parent-' + getID + ' .edit-field-charts').hide();
        $('#chart-parent-' + getID + ' label.chartname-' + getID).show();
        $('#chart-parent-' + getID + ' .modify-chart-btn').show();
        $('#chart-parent-' + getID + ' .delete-chart-btn').show();
        e.preventDefault();
    });

    $(document.body).on('click', '.charts-edit-btn', function(e) {
        var getID       = $(this).data('id'),
            addchart    = $('#chart-parent-' + getID + ' input[name="editchart"]').val(),
            acctnum     = $('#chart-parent-' + getID + ' input[name="acctnum"]').val(),
            acctname    = $('#chart-parent-' + getID + ' input[name="acctname"]').val();

        if ( addchart.length == '' ) {
            $('#chart-parent-' + getID + ' .chart-add-info').html('This field is required').show();
            setTimeout(function() {
                $('#chart-parent-' + getID + ' .chart-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else if ( acctnum.length == '' ) {
            $('#chart-parent-' + getID + ' .acctnum-add-info').html('This field is required').show();
            setTimeout(function() {
                $('#chart-parent-' + getID + ' .acctnum-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else if ( acctname.length == '' ) {
            $('#chart-parent-' + getID + ' .acctname-add-info').html('This field is required').show();
            setTimeout(function() {
                $('#chart-parent-' + getID + ' .acctname-add-info').fadeOut(300, function() {
                    $(this).hide();
                });
            }, 3500);
            return false;
        }

        else {
            $.ajax({
                type: 'POST', url: url, data: { id: getID, data: addchart, acctnum: acctnum, acctname: acctname },
                beforeSend: function() { $('#chart-parent-' + getID + ' .chartaccounts-input').addClass('loader-list'); },
                success: function() {
                    $('#chart-parent-' + getID + ' .chartaccounts-input').removeClass('loader-list');
                    $('#chart-parent-' + getID + ' label.chartname-' + getID).html(addchart);
                    $('.chartofaccounts-select option[value="' + getID + '"]').remove();
                    $('.chartofaccounts-select').append('<option value="' + getID + '">' + addchart + '</option>');
                    $('#chart-parent-' + getID + ' .edit-field-charts').hide();
                    $('#chart-parent-' + getID + ' label.chartname-' + getID).show();
                    $('#chart-parent-' + getID + ' .modify-chart-btn').show();
                    $('#chart-parent-' + getID + ' .delete-chart-btn').show();
                }
            });
        }

        e.preventDefault();
    });
}

// DELETE
function deleteChartdata(url) {
    $(document.body).on('click', '.delete-chart-btn', function(e) {
        var getID = $(this).data('id');

        if ( confirm('Are you sure you want to delete this chart of accounts\' data? ') ) {
            $.ajax({
                type: 'POST', url: url, data: { id: getID },
                beforeSend: function() { $('.modify-chart-list li#chart-parent-' + getID).addClass('loader-list'); },
                success: function(data) {
                    if ( data.info == 1 ) {
                        setTimeout(function() {
                            $('.modify-chart-list ul').append('<li class="empty-data" data-info="1">' + data.msg + '</li>');
                        }, 400);
                    }
                    $('.modify-chart-list li#chart-parent-' + getID).removeClass('loader-list');
                    $('.chartofaccounts-select option[value="' + getID + '"]').remove();
                    $('.modify-chart-list li#chart-parent-' + getID).fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            });
        }
        e.preventDefault();
    });
}