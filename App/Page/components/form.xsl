<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="form | form//*">
		<xsl:element name="{name()}">
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="form | form//*" mode="delete">
		<xsl:param name="title"/>
		<xsl:param name="message"/>
		<xsl:element name="{name()}">
			<xsl:if test="@type='submit'">
				<xsl:attribute name="title">
					<xsl:value-of select="$title"/>
				</xsl:attribute>
				<xsl:attribute name="onclick">
					<xsl:text>return confirm('</xsl:text>
					<xsl:value-of select="normalize-space($message)"/>
					<xsl:text>')</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="class">
					<xsl:text>btn btn-danger btn-sm</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
					<xsl:text>✖</xsl:text>
				</xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()" mode="delete">
				<xsl:with-param name="title" select="$title"/>
				<xsl:with-param name="message" select="$message"/>
			</xsl:apply-templates>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
