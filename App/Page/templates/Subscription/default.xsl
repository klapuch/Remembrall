<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:import href="../@layout.xsl"/>

    <xsl:template match="body">
        <xsl:apply-templates select="forms"/>
    </xsl:template>

    <xsl:template match="forms">
        <xsl:apply-templates select="subscribing"/>
    </xsl:template>

	<xsl:template match="subscribing">
		<xsl:copy-of select="./*"/>
    </xsl:template>

</xsl:stylesheet>
