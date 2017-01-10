<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="assets.xsl"/>

	<!-- To be overridden -->
	<xsl:template name="additionalStyles"/>

	<xsl:template match="head">
		<xsl:call-template name="styles"/>
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="title">
		<title><xsl:apply-templates/></title>
	</xsl:template>

	<xsl:template match="meta">
		<xsl:element name="meta">
			<xsl:attribute name="name">
				<xsl:apply-templates select="@name" mode="head"/>
			</xsl:attribute>
			<xsl:attribute name="content">
				<xsl:apply-templates select="@content" mode="head"/>
			</xsl:attribute>
		</xsl:element>
	</xsl:template>

	<xsl:template match="@*" mode="head">
		<xsl:value-of select="normalize-space(.)"/>
	</xsl:template>

</xsl:stylesheet>
