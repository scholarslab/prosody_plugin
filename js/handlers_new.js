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
        // checkrhyme(scheme, ans);
        event.preventDefault();
    });
});

function checkrhyme (argument) {
    // Checks whether the user entered rhyme is correct
}

function checkstress (argument) {
    // check the user entered stress
}

function checkfeet (argument) {
    // body...
}

function checkmeter (argument) {
    // body...
}

function switchstress (argument) {
    // Changes the stress
}


function switchfoot (argument) {
    // switch the foot
}


function togglestress (argument) {
    // body...
}

function togglefeet (argument) {
    // body...
}

function togglecaesura (argument) {
    // body...
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
