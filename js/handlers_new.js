// symbols used;
var space = '\u00A0';
var cup = '\u222a';
var backslash = '';

// function removeAllChildren (argument) {
//     // Will need to rewrite without prototype elements
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

    // Needs lines to set stress for each real syllable to be empty string
    // var realSpans = $('span[real]');
    // $.each(realSpans, function(index, object){
    //     object.stress = "";
    // });
    // console.log(realSpans);

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

function checkstress (argument) {
    // check the user entered stress
}


function checkmeter (argument) {
    // body...
}

function switchstress (argument) {
    // Changes the stress
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

    console.log(scheme);
    console.log(answer);

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

// function addMarker (argument) {
//     // body...
// }

// function marker (argument) {
//     // not sure what this does.
// }

// function slackMarker (argument) {
//     // body...
// }

// function footMarker (argument) {
//     // body...
// }

// function placeholder (argument) {
//     // ?
// }


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
