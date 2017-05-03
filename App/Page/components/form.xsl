<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="form | form//*">
		<xsl:element name="{name()}">
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="form | form//*" mode="button">
		<xsl:param name="title"/>
		<xsl:param name="class"/>
		<xsl:param name="value"/>
		<xsl:element name="{name()}">
			<xsl:if test="@type='submit'">
				<xsl:attribute name="title">
					<xsl:value-of select="$title"/>
				</xsl:attribute>
				<xsl:attribute name="class">
					<xsl:value-of select="$class"/>
				</xsl:attribute>
				<xsl:attribute name="value">
                    <xsl:value-of select="$value"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="@*"/>
			<xsl:apply-templates select="node()" mode="button">
				<xsl:with-param name="title" select="$title"/>
				<xsl:with-param name="class" select="$class"/>
				<xsl:with-param name="value" select="$value"/>
			</xsl:apply-templates>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
