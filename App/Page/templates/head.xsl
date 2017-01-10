<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="title">
		<title><xsl:apply-templates/></title>
	</xsl:template>

	<xsl:template match="meta">
		<xsl:element name="meta">
			<xsl:apply-templates select="@*" mode="head"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*" mode="head">
		<xsl:attribute name="{name()}">
			<xsl:value-of select="normalize-space(.)"/>
		</xsl:attribute>
	</xsl:template>

</xsl:stylesheet>
