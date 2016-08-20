<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:param name="assets" select="document('@headers.xml')/assets"/>

    <xsl:template name="meta">
        <meta name="description" content="{substring($description, 1, 150)}"/>
        <meta name="robots" content="index, follow"/>
        <meta name="author" content="Dominik Klapuch"/>
    </xsl:template>

    <xsl:template name="styles">
        <xsl:apply-templates select="$assets/styles/style"/>
    </xsl:template>

    <xsl:template match="style">
        <xsl:element name="link">
            <xsl:attribute name="rel">stylesheet</xsl:attribute>
            <xsl:attribute name="href">
                <xsl:value-of select="href"/>
            </xsl:attribute>
            <xsl:if test="integrity">
                <xsl:attribute name="integrity">
                    <xsl:value-of select="integrity"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="crossorigin">
                <xsl:attribute name="crossorigin">
                    <xsl:value-of select="crossorigin"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <xsl:template name="scripts">
        <xsl:apply-templates select="$assets/scripts/script"/>
    </xsl:template>

    <xsl:template match="script">
        <xsl:element name="script">
            <xsl:attribute name="src">
                <xsl:value-of select="src"/>
            </xsl:attribute>
            <xsl:if test="integrity">
                <xsl:attribute name="integrity">
                    <xsl:value-of select="integrity"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="crossorigin">
                <xsl:attribute name="crossorigin">
                    <xsl:value-of select="crossorigin"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:template>

</xsl:stylesheet>
