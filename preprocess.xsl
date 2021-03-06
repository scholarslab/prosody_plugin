<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:str="http://exslt.org/strings"
    extension-element-prefixes="str"
    xmlns:prosody="http://www.prosody.org" xmlns:TEI="http://www.tei-c.org/ns/1.0"
    xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:output indent="yes" method="xml" omit-xml-declaration="yes"/>
    <!-- <xsl:strip-space elements="*"/> -->
    <xsl:preserve-space elements="seg"/>
    <xsl:variable name="scheme">
      <xsl:for-each select="//TEI:lg/@rhyme">
        <xsl:value-of select="."/>
      </xsl:for-each>
    </xsl:variable>

    <xsl:template match="/">
			<xsl:if test="/TEI:TEI/TEI:text/TEI:body/TEI:lg[1]/@rhyme">
        <div id="rhyme" style="display:none;">
            <div id="rhymespacer"><xsl:text> </xsl:text></div>
            <form name="{$scheme}" id="rhymeform" autocomplete="off">
            <xsl:for-each select="/TEI:TEI/TEI:text/TEI:body/TEI:lg">
                <xsl:variable name="lgPos"><xsl:value-of select="position()"/></xsl:variable>
                <p><br/></p>
                <xsl:for-each select="TEI:l">
                    <div class="lrhyme">
                        <input size="1" maxlength="1" value="" name="lrhyme-{$lgPos}-{position()}" type="text" onFocus="this.value='';this.style['color'] = '#44FFFF';"/>
                    </div>
                </xsl:for-each>
            </xsl:for-each>
                <div class="lrhyme check"><input type="submit" value="&#x2713;" size="1" maxlength="1" id="rhymecheck"/></div>
            </form>
        </div>
        <div id="rhymebar">
            <xsl:text> </xsl:text>
        </div>
			</xsl:if>
        <div id="poem">
            <div id="poemtitle">
                <h2>
                    <xsl:apply-templates
                        select="/TEI:TEI/TEI:teiHeader/TEI:fileDesc/TEI:titleStmt/TEI:title"/>
                    <xsl:apply-templates
                        select="/TEI:TEI/TEI:teiHeader/TEI:fileDesc/TEI:publicationStmt/TEI:date"/>
                </h2>
                <xsl:if test="/TEI:TEI/TEI:teiHeader/TEI:fileDesc/TEI:titleStmt/TEI:author">
                    <h4>
                        <xsl:apply-templates
                            select="/TEI:TEI/TEI:teiHeader/TEI:fileDesc/TEI:titleStmt/TEI:author"/>
                    </h4>
                </xsl:if>
            </div>
            <xsl:apply-templates select="TEI:TEI/TEI:text/TEI:body/*"/>
        </div>
				<xsl:if test="/TEI:TEI/TEI:text/TEI:body/TEI:lg[1]/@rhyme">
        	<div id="rhymeflag">Rhyme</div>
				</xsl:if>
    </xsl:template>

    <xsl:template match="TEI:space">
        <!-- <span class="space_{@quantity}" /> -->
    </xsl:template>

		<xsl:template match="TEI:date">
			<small class="date">
				<xsl:text>(</xsl:text>
					<xsl:value-of select="."/>
				<xsl:text>)</xsl:text>
			</small>
		</xsl:template>

    <xsl:template match="TEI:lg">
        <xsl:apply-templates select="TEI:space" />
        <xsl:for-each select="TEI:l">
            <xsl:apply-templates select=".">
                <xsl:with-param name="linegroupindex" select="position()"/>
            </xsl:apply-templates>
        </xsl:for-each>
        <xsl:if test="not(./@rend='nobreak')">
              <br/>
        </xsl:if>
    </xsl:template>



    <xsl:template match="TEI:l">
        <xsl:param name="linegroupindex"/>
        <xsl:variable name="line-number" select="@n"/>
        <xsl:variable name="indent" select="@rend" />

        <div class="prosody-line {$indent}">
            <!-- first cycle through the segments, constructing shadow syllables -->
            <div class="prosody-shadowline" id="prosody-shadow-{$line-number}">
                <xsl:copy-of select="@*"/>
                <xsl:for-each select="TEI:seg">

                    <xsl:variable name="seg-position" select="position()"/>

                    <xsl:for-each select="text()|*/text()|TEI:caesura">
                    	<xsl:if test="name(.)='caesura'">
                        	<span class="caesura" style="display:none">//</span>
                        </xsl:if>
                        <xsl:variable name="foot-position" select="position()"/>
                        <xsl:variable name="foot-last" select="last()"/>
                        <xsl:variable name="node-string" select="."/>
                        <xsl:variable name="last-char-pos" select="string-length($node-string)"/>
                        <xsl:variable name="last-char" select="substring($node-string, $last-char-pos)"/>
                        <xsl:for-each select="str:tokenize($node-string,' ')">
                            <xsl:if test="string(.)">
                                <span class="prosody-shadowsyllable" shadow=""
                                    id="prosody-shadow-{$line-number}-{$seg-position}-{$foot-position}-{position()}"
                                    onclick="switchstress(this);">
                                    <span class="prosody-placeholder">
                                        <xsl:apply-templates/>
                                        <!-- <xsl:copy-of select="string(.)"/> -->
				<xsl:if test="not(position()=last())">
	                                        <xsl:text> </xsl:text>
	                                    </xsl:if>
                                    </span>
                                </span>
                            </xsl:if>
                        </xsl:for-each>
                        <xsl:if test="$last-char=' '">
                            <xsl:text> </xsl:text>
                        </xsl:if>
                    </xsl:for-each>
                    <xsl:if test="name(following-sibling::*[1]) = 'caesura'">
                        <span class="caesura" style="display:none">//</span>
                    </xsl:if>
                </xsl:for-each>
            </div>

            <div class="TEI-l" id="prosody-real-{$line-number}">
<!--                 <xsl:if test="exists(TEI:space)"> -->
                    <xsl:apply-templates select="TEI:space" />
<!--                 </xsl:if> -->

                <xsl:for-each select="@*">
                  <xsl:attribute name="data-{name()}"><xsl:value-of select="."/></xsl:attribute>
                </xsl:for-each>
                <xsl:attribute name="data-feet">
                  <xsl:for-each select="TEI:seg">
                    <xsl:if test="position()>1">|</xsl:if>
                    <xsl:value-of select="."/>
                  </xsl:for-each>
                </xsl:attribute>

                <span style="display:none;" linegroupindex="{$linegroupindex}" answer="{../@met}"
                    >Meter</span>


                <xsl:for-each select="TEI:seg">
                    <!-- if the following flag gets set, this indicates that there is a discrepancy in the line which must be later
                        highlighted -->
<!--                     <xsl:variable name="discrepant-flag" select="exists(@real)"/>
 -->
                         <xsl:variable name="discrepant-flag" select="boolean(@real)"/>

                    <!-- if the following flag gets set, this indicates that there is a sb element in the line and the
                    segment ends with a space -->

                    <xsl:variable name="seg-position" select="position()"/>
			<xsl:for-each select="text()|*/text()|TEI:caesura">
			<xsl:if test="name(.)='caesura'">
				<span class="caesura" style="display:none">//</span>
			</xsl:if>
                        <xsl:variable name="foot-position" select="position()"/>
                        <xsl:variable name="foot-last" select="last()"/>
                        <xsl:variable name="node-string" select="."/>
                        <xsl:variable name="last-char-pos" select="string-length($node-string)"/>
                        <xsl:variable name="last-char" select="substring($node-string, $last-char-pos)"/>
                        <xsl:for-each select="str:tokenize(.,' ')">
                            <xsl:if test="string(.)">
                                <span class="prosody-syllable" real=""
                                    id="prosody-real-{$line-number}-{$seg-position}-{$foot-position}-{position()}"
                                    onclick="switchfoot('prosody-real-{$line-number}-{$seg-position}-{$foot-position}-{position()}');">
                                    <xsl:if test="$discrepant-flag">
                                        <xsl:attribute name="discrepant"/>
                                    </xsl:if>
                                    <xsl:copy-of select="text()"/>
                                    <!-- add space back -->

                                    <xsl:choose>
                                      <xsl:when test="not(position()=last()) and $last-char=' '">
                                        <xsl:text> </xsl:text>
                                      </xsl:when>
                                      <xsl:when test="not(position()=last())">
                                        <xsl:text> </xsl:text>
                                      </xsl:when>
                                      <xsl:when test="$last-char=' '">
                                        <xsl:text> </xsl:text>
                                      </xsl:when>
                                    </xsl:choose>

                                    <xsl:if test="not(position()=last())">
                                        <!-- <xsl:text>A</xsl:text> -->
                                    </xsl:if>
                                    <!-- <span class="prosody-footmarker">|</span> -->
                                    <xsl:if test="$last-char=' '">
                                        <!-- <xsl:text>F</xsl:text> -->
                                    </xsl:if>
                                </span>
                            </xsl:if>
                        </xsl:for-each>

                    </xsl:for-each>
                    <xsl:if test="(name(following-sibling::*[1]) = 'caesura')">
                        <span class="caesura" style="display:none">//</span>
                    </xsl:if>
                </xsl:for-each>

            </div>
            <div class="buttons">
                  <xsl:if test="TEI:note">
                    <span class="button">
                        <button class="prosody-note-button" id="displaynotebutton{$line-number}"
                            name="Note about this line" onclick="">
                            <img src="[PLUGIN_DIR]/note.png"/>
                        </button>
                        <p class="prosody-note" id="hintfor{$line-number}">
                            <span>Note on line <xsl:value-of select="$line-number"/>:</span>
                            <xsl:value-of select="TEI:note"/>
                        </p>
                    </span>
                </xsl:if>
                <span class="button">
                    <button class="prosody-checkstress" id="checkstress{$line-number}"
                        name="Check stress" onclick="checkstress({$line-number})" onmouseover="Tip('Check stress', BGCOLOR, '#676767', BORDERWIDTH, 0, FONTCOLOR, '#FFF')" onmouseout="UnTip()">
                        <img src="[PLUGIN_DIR]/stress-default.png"/>
                    </button>
                </span>
                <span class="button">
                    <button class="prosody-checkfeet" id="checkfeet{$line-number}" name="Check feet"
                        onclick="checkfeet({$line-number})" onmouseover="Tip('Check feet', BGCOLOR, '#676767', BORDERWIDTH, 0, FONTCOLOR, '#FFF')" onmouseout="UnTip()">
                        <img src="[PLUGIN_DIR]/feet-default.png"/>
                    </button>
                </span>
                <span class="button">
                    <button class="prosody-meter" id="checkmeter{$line-number}" name="Check meter"
                        onclick="checkmeter({$line-number},{$linegroupindex})" onmouseover="Tip('Check meter', BGCOLOR, '#676767', BORDERWIDTH, 0, FONTCOLOR, '#FFF')" onmouseout="UnTip()">
                        <img src="[PLUGIN_DIR]/meter-default.png"/>
                    </button>
                </span>
            </div>

        </div>
    </xsl:template>
</xsl:stylesheet>
