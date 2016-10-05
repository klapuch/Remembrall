<?xml version="1.0" encoding="utf-8"?>
<html lang="cs-cz" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xsl:version='1.0'>
    <body>

        <xsl:element name="p">
            Hi, there are some changes on the
            <xsl:value-of select="/part/url"/>
            website with the
            <xsl:value-of select="/part/expression"/> expression
        </xsl:element>

        <xsl:element name="p">
            Check it out bellow this text
        </xsl:element>

        <xsl:element name="p">
            <xsl:value-of select="/part/content"/>
        </xsl:element>

    </body>
</html>


