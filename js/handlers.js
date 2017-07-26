// URLS for use throughout file
var siteUrl = WPURLS.siteurl;
var correctAnswerUrl = siteUrl + "/wp-content/plugins/prosody_plugin/images/correct.png";
var incorrectAnswerUrl = siteUrl + "/wp-content/plugins/prosody_plugin/images/incorrect.png";
var expectedAnswerUrl = siteUrl + "/wp-content/plugins/prosody_plugin/images/expected.png";
// This is a hack. We're using it to shave off a pixel to get the shadowsyllables to correctly sit on top of the real syllables.
var STRESS_WIDTH = 1;

if (!String.prototype.endsWith) {
  String.prototype.endsWith = function(searchString, position) {
      var subjectString = this.toString();
      if (position === undefined || position > subjectString.length) {
        position = subjectString.length;
      }
      position -= searchString.length;
      var lastIndex = subjectString.indexOf(searchString, position);
      return lastIndex !== -1 && lastIndex === position;
  };
}

$(document).ready(function(){

    // Set initial stress to an empty string for all real spans
    var realSpans = $('span[real]');
    realSpans.attr('data-stress', '');

    var poemHeight = $('#poem').height();
    var rhymeHeight = poemHeight + 50;
    $('#rhymebar').height(rhymeHeight + 'px');
    $('#rhyme').height(rhymeHeight + 'px');

    var titleHeight = $('#poemtitle').height();
    var spacerHeight = titleHeight + 44;
    $('#rhymespacer').height(spacerHeight + 'px');

    // Set initial width of shadowsyllables
    setTimeout(function(){
      var shadowSyllables = $('.prosody-shadowsyllable');
      shadowSyllables.each(function (i) {
          var correspondingRealSyllable = $('#prosody-real-' + shadowSyllables[i].id.substring(15));
          var correspondingRealSyllableWidth = correspondingRealSyllable.innerWidth();
          shadowSyllables[i].style.width = correspondingRealSyllableWidth + 'px';
      });
    }, 500);

    // Click handlers for toggles
    $('#togglestress').click(function(){
        togglestress(this);
    });
    $('#togglefeet').click(function(){
        togglefeet(this);
    });
    $('#togglecaesura').click(function(){
        togglecaesura(this);
    });
    $('#togglediscrepancies').click(function(){
      toggledifferences(this);
    });

    // Hide the syncopation checkbox
    $('#syncopation').hide();

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

function checkmeter ( lineNumber, lineGroupIndex ) {
    var fullAnswer = $('#prosody-real-' + lineNumber + " span[answer]").attr('answer');
    if (!fullAnswer) {
      console.log("There is no answer given for this line.");
    }
    var footType = fullAnswer.split('(')[0];
    var numberFeet = fullAnswer.match(/\d+/g)[lineGroupIndex-1];
    var correctAnswer = footType + numberFeet;

    $('#check-answer').one("click", function () {
        var footScheme = $('#foot-select').val();
        var numberScheme = $('#number-select').val();
        var fullScheme = footScheme + numberScheme;

        if ( correctAnswer === fullScheme ) {
            $('#checkmeter' + lineNumber + " img").attr("src", correctAnswerUrl);
        } else {
            $('#checkmeter' + lineNumber + " img").attr("src", incorrectAnswerUrl);
        }

        $('#meter-select').dialog( "close" );
      showSyncopation();
    });

    $('#meter-select').dialog( "open" );

}

function switchstress (shadowSyllable) {
    var realSyllable = $('#prosody-real-' + shadowSyllable.id.substring(15));
    var stress = realSyllable.attr('data-stress');

    if( stress === '-' || stress === '' ) {
        $('#' + shadowSyllable.id).fadeIn();
        $('#' + shadowSyllable.id).empty();
        $('#' + shadowSyllable.id).append(marker(realSyllable));
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
            realSyllable.attr('data-stress', '-');
        }, 150);
        $('#' + shadowSyllable.id).fadeIn();
    }

    var digits = /\d+/;
    var sub = digits.exec(shadowSyllable.id);
    var shadowLineNumber = '';
    if (sub !== null) {
      shadowLineNumber = sub[0];
    }

    $('#checkstress' + shadowLineNumber + ' img').attr('src', siteUrl + '/wp-content/plugins/prosody_plugin/images/stress-default.png');
}

function checkstress ( lineNumber ) {
    // Scheme is the user submitted stress marks
    var scheme = '';
    $('#prosody-real-' + lineNumber + ' span[real]').each(
        function () {
            var syllableStress = this.dataset.stress;
            scheme += syllableStress;
        }
    );

    // Make sure the answer is complete
    var answerLength = $('#prosody-real-' + lineNumber + ' span[real]').length;
    var schemeLength = scheme.length;

    if ( answerLength !== schemeLength ) {
        alert("An answer must be complete to be submitted. Please fill in a symbol over each syllable in this line.");
        return;
    }

    var answer = $('#prosody-real-' + lineNumber).data('real').split('|');
    var realAnswer = answer[0].replace(/-/g, '\u222a');
    // Remove the parentheses that some poems have for optional stress marks
    realAnswer = realAnswer.replace(/\(|\)/g, '');
    var expectedAnswer;
    // if answer[1] exists, and answer[1] does not equal answer[0]
    if ( answer[1] && answer[1] !== answer[0] ) {
        expectedAnswer = answer[1].replace(/-/g, '\u222a');
    }

    
    if ( scheme === realAnswer ) {
        $("#checkstress" + lineNumber + " img").attr("src", correctAnswerUrl);
        showNote(lineNumber);
    } else if ( scheme === expectedAnswer) {
        $("#checkstress" + lineNumber + " img").attr("src", expectedAnswerUrl);
        showNote(lineNumber);
    } else {
        $("#checkstress" + lineNumber + " img").attr("src", incorrectAnswerUrl);
    }

    showSyncopation();

    // Show notes if there are any
    $('#displaynotebutton' + lineNumber).click(function(){
        $('#hintfor' + lineNumber).hide();
        togglenote(lineNumber);
    });
}

function showNote(lineNumber) {
  $("#displaynotebutton" + lineNumber).show();
}

function showSyncopation() {
  var corrects = $('img[src$="images/correct.png"]');
  var expecteds = $('img[src$="images/expected.png"]');
  var totalCorrect = corrects.length + expecteds.length;
  var numLines = $('.prosody-line');
  if (totalCorrect === numLines.length * 3){
    $('#syncopation').show();
  } else if (totalCorrect !== numLines.length){
    $('#syncopation').hide();
  }
}

function switchfoot ( syllableId ) {

  var syllableSpan = $('#' + syllableId + ' span');
    if ( syllableSpan.length === 0 ) {
        $('#' + syllableId).append('<span class="prosody-footmarker">|</span>');
        syllableSpan = $('#' + syllableId + ' span');
    } else {
        $('#' + syllableId + ' .prosody-footmarker').remove();
    }

    var digit_search = /\d+/;
    var digit_group = digit_search.exec(syllableId);
    var shadowLineNumberSection = '';
    if (digit_group !== null) {
      shadowLineNumberSection = digit_group[0];
    }

    $("#checkfeet" + shadowLineNumberSection + " img").attr("src", siteUrl + "/wp-content/plugins/prosody_plugin/images/feet-default.png");

    var footSyllable = $('#' + syllableId);
    var footSyllableWidth = footSyllable.width();
    var footSyllableId = footSyllable.attr('id').substring(13);
    var footShadowSyllable = $('#prosody-shadow-' + footSyllableId);
    setTimeout(function(){
      footShadowSyllable.width(footSyllableWidth - STRESS_WIDTH);
    }, 100);
}

function checkfeet ( lineNumber ) {
    var scheme = $('#prosody-real-' + lineNumber + ' span[real]').text();
    var answer = $('#prosody-real-' + lineNumber).data('feet');
    if ( scheme.endsWith('|')) {
        scheme = scheme.slice(0, -1);
    }

   // remove everything but words, numbers and pipe
    answer = answer.replace(/[^\w|]/g, '');
    scheme = scheme.replace(/[^\w|]/g, '');

    if ( scheme === answer) {
        $("#checkfeet" + lineNumber + " img").attr("src", correctAnswerUrl);
    } else {
        $("#checkfeet" + lineNumber + " img").attr("src", incorrectAnswerUrl);
    }

    showSyncopation();
}

function togglenote(lineNumber) {
  if($('#hintfor' + lineNumber).css('display') === 'none'){
    $('#hintfor' + lineNumber).dialog();
    $('#hintfor' + lineNumber).show();
  } else {
    $('#hintfor' + lineNumber).dialog('close');
    $('#hintfor' + lineNumber).hide();
  }
}

function togglestress ( node ) {
    $('.prosody-marker').each(function(i, el){
        if(node.checked){
            $(el).show();
        } else {
            $(el).hide();
        }
    });

}

function togglefeet (node) {
    $('.prosody-footmarker').each(function(i, el){
        if(node.checked){
            $(el).show();
        } else {
            $(el).hide();
        }
    });
}

function togglecaesura (node) {
    $('.caesura').each(function(i, el){
        if(node.checked){
            $(el).show();
        } else {
            $(el).hide();
        }
    });
}

function toggledifferences (node) {
    if (node.checked){
      $('span[discrepant]').addClass('discrep');
    } else {
      $('span[discrepant]').removeClass('discrep');
    }
}

function addMarker ( real, symbol ) {
    var prosodyMarker = document.createElement("span");
    prosodyMarker.className = "prosody-marker";

    prosodyMarker.textContent = symbol;
    return prosodyMarker;
}

function marker ( real ) {
    return addMarker( real, "/" ) ;
}

function slackmarker ( real ) {
    return addMarker ( real, "\u222A" );
}

function placeholder ( real ) {
    return addMarker( real, " " );
}

function decodeEntities (encodedString) {
  var textArea = document.createElement('textarea');
  textArea.innerHTML = encodedString;
  return textArea.value;
}
