// symbols used;
var space = '\u00A0';
var cup = '\u222a';
var backslash = '';

// function empty () {
//     $(this).empty();
// }

// function getInnerText (argument) {
//     // body...
// }

// function pullText (argument) {
//     // what's the difference between this and getInnerText
// }

// function init (argument) {
//     // this does a lot. Switch to using jquery on event
//     Seems like all of this should be done in a document ready block
// }
//
$(document).ready(function(){

    // Set initial stress to an empty string for all real spans
    var realSpans = $('span[real]');
    realSpans.attr('data-stress', '');

    var poemHeight = $('#poem').height();
    var rhymeHeight = poemHeight + 20;
    $('#rhymebar').height(rhymeHeight + 'px');
    $('#rhyme').height(rhymeHeight + 'px');

    var titleHeight = $('#poemtitle').height();
    var spacerHeight = titleHeight + 44;
    $('#rhymespacer').height(spacerHeight + 'px');

    // initialize watch events to toggle the rhymebar
    $('#rhymebar').on('click', function(){
        $('#rhyme').toggle();
    });
    $('#rhymeflag').on('click', function(){
        $('#rhyme').toggle();
    });

    // watch for rhymeform submission and set scheme and answer
    $('#rhymeform').on('submit', function(event){
        var scheme = $('#rhymeform').attr('name').replace(/\s/g, "");
        var ans = "";

        var total = $('#rhymeform :input[type=text]');
        $.each( total, function(index, object){
            ans += object.value;
        });
        checkrhyme(scheme, ans);
        event.preventDefault();
    });
});

function checkrhyme (scheme, answer) {
    if (scheme === answer) {
        $('#rhymecheck').addClass('right');
        $('#rhymecheck').removeClass('wrong');
        $('#rhymecheck').val('\u2713');
    } else {
        $('#rhymecheck').addClass('wrong');
        $('#rhymecheck').removeClass('right');
        $('#rhymecheck').val('X');
    }
}

function checkmeter (argument) {
    // body...
}

function switchstress (shadowSyllable) {
    var realSyllable = $('#prosody-real-' + shadowSyllable.id.substring(15));
    var stress = realSyllable.attr('data-stress');
    console.log(stress);

    if( stress === '-' || stress === '' ) {
        $('#' + shadowSyllable.id).fadeIn();
        $('#' + shadowSyllable.id).empty();
        $('#' + shadowSyllable.id).append(marker(realSyllable));
        // Need to rewrite below line with jquery
        // realSyllable.stress = '+';
        realSyllable.attr('data-stress', '+');
    } else if ( stress === "+") {
        $('#' + shadowSyllable.id).fadeOut();
        setTimeout(function () {
            $('#' + shadowSyllable.id).empty();
            $('#' + shadowSyllable.id).append(slackmarker(realSyllable));
            realSyllable.attr('data-stress', '\u222a');
        }, 150);
        $('#' + shadowSyllable.id).fadeIn();
    } else {
        $('#' + shadowSyllable.id).fadeOut();
        setTimeout(function () {
            $('#' + shadowSyllable.id).empty();
            $('#' + shadowSyllable.id).append(placeholder(realSyllable));
            // Need to rewrite below line
            // realSyllable.stress = '-';
            realSyllable.attr('data-stress', '-');
        }, 150);
        $('#' + shadowSyllable.id).fadeIn();
    }

    $('#checkstress' + shadowSyllable.id + 'img').attr('src', 'wp-content/plugins/prosody_plugin/images/stress-default.png');
}

function checkstress (argument) {
    // check the user entered stress
}

function switchfoot ( syllableId ) {
    var syllableSpan = $('#' + syllableId + ' span');
    if ( syllableSpan.length === 0 ) {
        $('#' + syllableId).append('<span class="prosody-footmarker">|</span>');
        syllableSpan = $('#' + syllableId + ' span');
    } else {
        $('#' + syllableId + ' .prosody-footmarker').remove();
    }

    $("#checkfeet" + syllableId.substring(13,14) + " img").attr("src", "wp-content/plugins/prosody_plugin/images/feet-default.png");
}

function checkfeet ( lineNumber ) {
    var scheme = $('#prosody-real-' + lineNumber + ' span[real]').text();
    var answer = $('#prosody-real-' + lineNumber).data('feet');
    if ( scheme.endsWith('|')) {
        scheme = scheme.slice(0, -1);
    }
    scheme = scheme.replace(/\s+/g, '');
    answer = answer.replace(/\s+/g, '');

    if ( scheme === answer) {
        $("#checkfeet" + lineNumber + " img").attr("src", "wp-content/plugins/prosody_plugin/images/correct.png");
    } else {
        $("#checkfeet" + lineNumber + " img").attr("src", "wp-content/plugins/prosody_plugin/images/incorrect.png");
    }
}

function togglestress () {
    // Reproducing this from original js - does this each work? Will test after getting stress marks working
    $('.prosody-marker').each(function(el){
        if($('#togglestress').val()){
            el.show();
        } else {
            el.hide();
        }
    });
}

function togglefeet (argument) {
    $('.prosody-footmarker').each(function(el){
        if($('#togglefeet').val()){
            el.show();
        } else {
            el.hide();
        }
    });
}

function togglecaesura (argument) {
    $('.caesura').each(function(el){
        if($('#togglecaesura').val()){
            el.show();
        } else {
            el.hide();
        }
    });
}

function toggledifferences (argument) {
    // body...
}

// function grabText (argument) {
//     // what is the difference between this and getInnerText/pullText
// }

function addMarker ( real, symbol ) {
    // var prosodyMarker = '<span class="prosody-marker"></span>';
    var prosodyMarker = document.createElement("span");
    prosodyMarker.className = "prosody-marker";

    var syllableText = real.text();

    var textLength = syllableText.length;
    var textMiddle = Math.floor(textLength/2);

    var textMod = textLength % 2;
    // var spacer = "\u00A0".times(textMiddle);
    var spacer = '';

    for (var i = textMiddle - 1; i >= 0; i--) {
        spacer = spacer + '\u00A0';
    }

    if ( textMod === 0) {
        // var lspacer = "\u00A0".times(textMiddle -1);
        var lspacer = '';
        for (var j = textMiddle - 2; j >= 0; j--) {
            lspacer = lspacer + '\u00A0';
        }
        prosodyMarker.textContent = lspacer + symbol + lspacer + "\u00A0";
    } else {
        prosodyMarker.textContent = spacer + symbol + spacer;
    }
    console.log('addMarker');
    return prosodyMarker;

}

function marker ( real ) {
    console.log('Marker');

    return addMarker( real, "/" ) ;
}

function slackmarker ( real ) {
    console.log('slackMarker');

    return addMarker ( real, "\u222A" );
}

// I've defined this function, but am leaving it commented out as it not otherwise referenced in the application.
// function footmarker ( real ) {
//     var footMark = document.createElement('span');
//     footMark.className = "prosody-footmarker";
//     footMark.appendChild(document.createTextNode('|'));
//     return footMark;
// }

function placeholder ( real ) {
    console.log('placeholder');

    return addMarker( real, " " );
}


// function updateHintButton (argument) {
//     // probably not necessary since hints aren't currently showing up.
// }

// function popHint (argument) {
//     // also probably no longer necessary
// }

// function clickableHintImage (argument) {
//     // also probably no longer necessary
// }

// function debug (argument) {
//     // necessary?
// }
