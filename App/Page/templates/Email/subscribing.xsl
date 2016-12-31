<?xml version="1.0" encoding="utf-8"?>
<html xmlns:xsl="http://www.w3.org/1999/XSL/Transform" lang="cs-cz" xsl:version='1.0'>
    <body>

        <xsl:element name="p">
			<xsl:text>Hi, there are some changes on </xsl:text>
            <xsl:value-of select="/part/url"/>
			<xsl:text> website with </xsl:text>
			<xsl:value-of select="/part/expression"/>
			<xsl:text> expression</xsl:text>
        </xsl:element>

		<p>Check it out bellow this text</p>
		<br/>
		<p><xsl:value-of select="/part/content"/></p>

    </body>
</html>


