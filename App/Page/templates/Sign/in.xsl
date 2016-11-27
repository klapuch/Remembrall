<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:import href="../@layout.xsl"/>

    <xsl:param name="title" select="template/default/title"/>
    <xsl:param name="description" select="template/default/description"/>

    <xsl:template match="template">
        <xsl:apply-templates select="forms"/>
    </xsl:template>

    <xsl:template match="forms">
        <xsl:apply-templates select="in"/>
    </xsl:template>

    <xsl:template match="in">
        <xsl:copy-of select="./*"/>
    </xsl:template>

</xsl:stylesheet>
