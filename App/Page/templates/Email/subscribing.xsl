<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="utf-8" indent="yes"/>
    <xsl:template match="/part">
        <html>
            <body>
                <xsl:element name="p">
                    Hi, there are some change on the
                    <xsl:value-of select="url"/>
                    website with the
                    <xsl:value-of select="expression"/> expression
                </xsl:element>
                <xsl:element name="p">
                    Check it out bellow this text
                </xsl:element>
                <xsl:element name="p">
                    <xsl:value-of select="$content"/>
                </xsl:element>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>


