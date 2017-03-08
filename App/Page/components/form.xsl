<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="xsl:element">
		<xsl:element name="{@name}">
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="xsl:attribute">
		<xsl:attribute name="{@name}">
			<xsl:apply-templates/>
		</xsl:attribute>
	</xsl:template>

	<xsl:template match="form | form//*[not(self::xsl:attribute or self::xsl:element)]">
		<xsl:element name="{name()}">
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()"/>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
