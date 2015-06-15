- Need to be able to manipulate stress markers, feet

    - collection of symbols/characters for each variation
    - function for each of four markers such that on click, the app rotates through the markers for that type
    - function to highlight where markers can be placed for ui

- switch stress
    - on click of span.prosody-shadowsyllable, cycle through predefined stress symbols - each symbol is span.prosody-marker within the span.prosody-shadowsyllable

- switch foot
    - function such that clicking a .prosody-syllable creates a pipe after the syllable - a span.prosody-footmarker

- check meter
    - create popup with two dropdown elements - meter, number of feet

- check stress
    - check stress that are currently in the shadow line (collection of shadow syllables) match element in the .prosody-shadowline

- check feet
    - check feet that are currently in the line; match element in the .prosody-shadowline (?).

- check * buttons ()
    - for each of three buttons (check stress, check feet, check meter), display small popup on hover that says what button does
    - on click, runs function to actually check correctness. If incorrect, shows red x icon, if correct, shows yellow asterisk image (images/expected.png)

