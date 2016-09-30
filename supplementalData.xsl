<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
        version="1.0"
        xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
        xmlns:str="http://exslt.org/strings"
        extension-element-prefixes="str"
>
    <xsl:output
            method="text"
            omit-xml-declaration="yes"
            indent="no"
            media-type="text/plain"/>

    <xsl:template match="/">
        <xsl:apply-templates select="/supplementalData/languageData/language[@territories]"/>
    </xsl:template>

    <xsl:template match="language">
        <xsl:variable name="lang_code" select="@type"/>
        <xsl:value-of select="$lang_code"/>

        <xsl:for-each select="str:split(@territories, ' ')">
            <xsl:variable name="country_abbr" select="."/>
            <xsl:value-of select="$country_abbr"/>
            <xsl:value-of select="/supplementalData/territoryInfo/territory[@type=$country_abbr]/languagePopulation[@type=$lang_code]/@population"/>
        </xsl:for-each>
        <xsl:text>

</xsl:text>
    </xsl:template>

</xsl:stylesheet>