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
		<title>
			<xsl:choose>
				<xsl:when test="normalize-space(.) = ''">
					<xsl:text>Remembrall</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="normalize-space(.)"/>
				</xsl:otherwise>
			</xsl:choose>
		</title>
	</xsl:template>

	<xsl:template match="description">
		<xsl:call-template name="meta" mode="head">
			<xsl:with-param name="description" select="."/>
		</xsl:call-template>
	</xsl:template>

	<!-- Todo: Separate to meta headers  -->
	<xsl:template name="meta" mode="head">
		<xsl:param name="description"/>
		<xsl:if test="$description != ''">
			<meta
				name="description"
				content="{substring(normalize-space($description), 1, 150)}"
			/>
		</xsl:if>
		<meta name="robots" content="index, follow"/>
		<meta name="author" content="Dominik Klapuch"/>
	</xsl:template>

</xsl:stylesheet>
