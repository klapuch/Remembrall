<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="tabs" mode="parts">
		<xsl:param name="baseUrl"/>
		<ul class="nav nav-tabs">
			<xsl:apply-templates select="tab" mode="parts">
				<xsl:with-param name="baseUrl" select="$baseUrl"/>
			</xsl:apply-templates>
		</ul>
	</xsl:template>

	<xsl:template match="tab" mode="parts">
		<xsl:param name="baseUrl"/>
		<xsl:element name="li">
			<xsl:attribute name="role">presentation</xsl:attribute>
			<xsl:if test="@state">
				<xsl:attribute name="class">
					<xsl:value-of select="@state"/>
				</xsl:attribute>
			</xsl:if>
			<a href="{$baseUrl}parts/{@href}"><xsl:value-of select="."/></a>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
