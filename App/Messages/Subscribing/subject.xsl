<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output method="html" indent="yes" omit-xml-declaration="yes" />

	<xsl:template match="part">
		<xsl:text>Changes occurred on </xsl:text>
		<xsl:apply-templates select="url"/>
		<xsl:text> page with </xsl:text>
		<xsl:apply-templates select="expression"/>
		<xsl:text> expression</xsl:text>
	</xsl:template>

</xsl:stylesheet>
