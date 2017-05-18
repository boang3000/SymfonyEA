$(function() {
    $(".account").on('click', function() {
        var X = $(this).attr('id');

        if( X == 1 ) {
            $(".submenu").hide();
            $(this).attr('id', '0');
        } else {
            $(".submenu").show();
            $(this).attr('id', '1');
        }
    });

    // mouseup textarea false
    $(".submenu").on('mouseup', function() {
        return false
    });
    $(".account").on('mouseup', function() {
        return false
    });

    // textarea without editing.
    $(document).on('mouseup', function() {
        $(".submenu").hide();
        $(".account").attr('id', '');
    });

    // SEARCH

    // Advanced search
    $(".icon").click(function() {
        $(".advanced-search").slideToggle();

        if (search.ctr == 0)
            search.addFilter();
    });

    $("#add-searchfield").click(function() {
        search.addFilter();
    });
});

function alphaNumeric(string) {
    $(string).keypress(function (e) {
        if ((e.which < 65 || e.which > 90) &&
            (e.which < 97 || e.which > 122) &&
            e.which != 32 &&
            e.which != 8 &&
            e.which != 0 &&
            e.which != 45 &&
            (e.which < 48 || e.which > 57) &&
            e.which != 481) {
            return false;
        }
    });
}

function numbersOnly(number) {
    $(number).keypress(function (e) {
        if (e.which != 8 &&
            e.which != 0 &&
            (e.which < 48 || e.which > 57) &&
             e.which != 481) {
            return false;
        }
    });
}

function numbersDotOnly(number) {
    $(number).keypress(function (e) {
        if (e.which != 8 &&
            e.which != 0 &&
            (e.which < 48 || e.which > 57) &&
             e.which != 481 &&
			 e.which != 46) {
            return false;
        }
    });
}

function lettersOnly(letter) {
    $(letter).keypress(function (e) {
        if ((e.which < 65 || e.which > 90) &&
            (e.which < 97 || e.which > 122) &&
            e.which != 32) {
            return false;
        }
    });
}

function spNumbersOnly(keyword) {
    $(keyword).keypress(function (e) {
        if (e.which != 8 &&
            e.which != 0 &&
            (e.which < 48 || e.which > 57) &&
            e.which != 481 &&
            e.which != 44 &&
            e.which != 46) {
            return false;
        }
    });
}

// included- "space", "comma", "period"
function specialCharAlphaNumeric2(keyword) {
    $(keyword).keypress(function (e) {
        if ((e.which < 65 || e.which > 90) &&
            (e.which < 97 || e.which > 122) &&
            (e.which < 48 || e.which > 57) &&
            e.which != 8 &&
            e.which != 0 &&
            e.which != 40 &&
            e.which != 41 &&
            e.which != 44 &&
            e.which != 45 &&
            e.which != 46 &&
            e.which != 48 &&
            e.which != 32) {
            return false;
        }
    });
}

// included- "space", "comma", "period", "#"
function vendorAddress(keyword) {
    $(keyword).keypress(function (e) {
        if ((e.which < 65 || e.which > 90) &&
            (e.which < 97 || e.which > 122) &&
            (e.which < 48 || e.which > 57) &&
            e.which != 8 &&
            e.which != 0 &&
            e.which != 40 &&
            e.which != 41 &&
            e.which != 44 &&
            e.which != 45 &&
            e.which != 46 &&
            e.which != 48 &&
            e.which != 35 &&
            e.which != 32) {
            return false;
        }
    });
}

function validateBankAcct(selector) {
    var bankaccount     = $(selector),
        getbankaccount  = bankaccount.html(),
        renewNum        = (getbankaccount.length - 4)+1,
        string          = '';

    for ( var i = 0; i < renewNum; i++) {
        string = getbankaccount.charAt(i);
        string = string.replace(string, '#');
    }

    bankaccount.html('...' + getbankaccount.slice(-4));
}

function IsEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function generateUUID() {
    var d = new Date().getTime();
    var uuid = 'xxxxxxxxFxxxx+4xxx/yxxxOxxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random()*16)%16 | 0;
        d = Math.floor(d/16);
        return (c=='x' ? r : (r&0x7|0x8)).toString(16);
    });
    return uuid;
}

$.fn.digits = function(){
    return this.each(function(){
        $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") );
    })
}

$.fn.hasAttr = function(name) {
    return this.attr(name) !== undefined;
};

var search = {
    ctr: 0,

    addFilter : function() {
        var searchForm = $("#search-form"),
            module = $('select[name="module"]').val(),
            options = {
                Invoices : ['Invoice No', 'Entity', 'Bank', 'Bank Account', 'Routing Balance', 'Vendor'],
                Entities : ['Entity', 'Bank', 'Bank Account'],
                Registry : ['Invoice No', 'Entity', 'Bank', 'Bank Account', 'Vendor']
            },
            url = {
                Invoices : $('input[name="invoicesUrl"]').val(),
                Entities : $('input[name="entitiesUrl"]').val(),
                Registry : $('input[name="registryUrl"]').val()
            };

        if (!module) {
            return;
        }

        this.ctr++;

        var select = $("<select></select>").attr('name', 'field_' + this.ctr)
            .append('<option>All Fields</option>');

        for (i in options[module]) {
            select.append('<option>' + options[module][i] + '</option>');
        }

        $('.advanced-search form').attr('action', url[module]);

        var text = $("<input>")
            .attr('type', 'text')
            .attr('name', 'search_' + this.ctr)
            .attr('required', 'true');

        var button = $("<input>")
            .attr('type', 'image')
            .attr('src', '/bundles/arcanyseasyapp/img/info_close.png')
            .attr('onclick', "search.removeFilter(this); return false;");

        var div = $("<div></div>")
            .append(select).append(text).append(button);

        searchForm.append(div);

        $("input[name='filter']").val(this.ctr);
    },

    removeFilter : function(a) {
        if (this.ctr > 1) {
            $(a).parent().remove();

            this.ctr -= 1;
            $("input[name='filter']").val(this.ctr);
        }
    }

};

$(document).on('change', '.btn-group select[name="module"]', function(e) {
    var me = $(this),
        searchForm = $("#search-form");

    searchForm.empty();
    search.ctr = 0;

    search.addFilter();
});