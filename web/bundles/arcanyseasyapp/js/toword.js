// Convert numbers to words
// copyright 25th July 2006, by Stephen Chapman http://javascript.about.com
// permission to use this Javascript on your web page is granted
// provided that all of the code (including this copyright notice) is
// used exactly as shown (you can change the numbering system if you wish)

// American Numbering System
var th = ['','thousand','million', 'billion','trillion'];
// uncomment this line for English Number System
// var th = ['','thousand','million', 'milliard','billion'];

var dg = ['','one','two','three','four', 'five','six','seven','eight','nine'];
var tn = ['ten','eleven','twelve','thirteen', 'fourteen','fifteen','sixteen', 'seventeen','eighteen','nineteen'];
var tw = ['twenty','thirty','forty','fifty', 'sixty','seventy','eighty','ninety'];

function toWords(s){
    s = s.toString();
    s = s.replace(/[\, ]/g,'');

    if (s != parseFloat(s))
        return 'not a number';

    var x = s.indexOf('.');

    if (x == -1)
        x = s.length;

    if (x > 15)
        return 'too big';

    var n = s.split('');
    var str = '';
    var sk = 0;

    for (var i=0; i < x; i++) {
        if ((x-i)%3==2) {
            if (n[i] == '1') {
                str += tn[Number(n[i+1])] + ' ';
                i++;
                sk=1;
            }
            else if (n[i]!=0) {
                str += tw[n[i]-2] + ' ';
                sk=1;
            }
        }
        else if (n[i]!=0) {
            str += dg[n[i]] +' ';
            if ((x-i)%3==0)
                str += 'hundred ';
                sk=1;
        }

        if ((x-i)%3==1) {
            if (sk)
                str += th[(x-i-1)/3] + ' ';
                sk=0;
        }
    }

    /*if (x != s.length) {
        var y = s.length;
        str += 'and '; // point
        for (var i=x+1; i<y; i++)
            str += dg[n[i]] +' ';
        console.log(y);
    }*/

    return str.replace(/\s+/g,' ');
}


// PART 2
var NUMBER2TEXT = {
    ones: ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'],
    tens: ['', '', 'twenty', 'thirty', 'fourty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'],
    sep: ['', ' thousand ', ' million ', ' billion ', ' trillion ', ' quadrillion ', ' quintillion ', ' sextillion ']
};

(function( ones, tens, sep ) {

    var input   = document.getElementById( 'input-cur-amount' ),
        output  = document.getElementById( 'output-cur-amount' );

    if ( input ) {
        input.onkeyup = function() {
            var val = this.value,
                val = val.replace(",", ""),
                arr = [],
                str = '',
                i = 0;

            if ( val.length === 0 ) {
                output.textContent = 'Please enter a check amount.';
                return;
            }

            val = parseInt( val, 10 );
            if ( isNaN( val ) ) {
                output.textContent = 'Invalid input.';
                return;
            }

            while ( val ) {
                arr.push( val % 1000 );
                val = parseInt( val / 1000, 10 );
            }

            while ( arr.length ) {
                str = (function( a ) {
                    var x = Math.floor( a / 100 ),
                        y = Math.floor( a / 10 ) % 10,
                        z = a % 10;

                    return ( x > 0 ? ones[x] + ' hundred ' : '' ) +
                        ( y >= 2 ? tens[y] + ' ' + ones[z] : ones[10*y + z] );
                })( arr.shift() ) + sep[i++] + str;
            }

            output.textContent = str;
        };
    }

})( NUMBER2TEXT.ones, NUMBER2TEXT.tens, NUMBER2TEXT.sep );