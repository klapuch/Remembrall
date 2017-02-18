<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="style">
		<xsl:element name="link">
            <xsl:attribute name="rel">stylesheet</xsl:attribute>
			<xsl:apply-templates select="@*" mode="assets"/>
		</xsl:element>
    </xsl:template>

    <xsl:template match="script">
        <xsl:element name="script">
			<xsl:apply-templates select="@*" mode="assets"/>
        </xsl:element>
	</xsl:template>

	<xsl:template match="@*" mode="assets">
		<xsl:attribute name="{name()}">
			<xsl:value-of select="normalize-space(.)"/>
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>
