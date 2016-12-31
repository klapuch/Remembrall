<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="assets.xsl"/>

	<!-- To be overridden !-->
    <xsl:template name="additionalStyles"/>

	<xsl:template name="head">
		<xsl:param name="title"/>
		<xsl:param name="description"/>
		<title>
			<xsl:choose>
				<xsl:when test="normalize-space($title) = ''">
					<xsl:text>Remembrall</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="normalize-space($title)"/>
				</xsl:otherwise>
			</xsl:choose>
		</title>
		<xsl:call-template name="meta" mode="head">
			<xsl:with-param name="description" select="$description"/>
		</xsl:call-template>
		<xsl:call-template name="styles"/>
		<xsl:call-template name="additionalStyles"/>
	</xsl:template>

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
