<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../templates/@layout.xsl"/>
    <xsl:import href="../../components/form.xsl"/>

	<xsl:template match="page">
		<h1><xsl:apply-templates select="body/header[@level=1]"/> of <xsl:apply-templates select="part" mode="title"/></h1>
		<xsl:apply-templates select="part"/>
        <xsl:apply-templates select="forms/form[@name='new']"/>
	</xsl:template>

	<xsl:template match="part">
		<xsl:value-of disable-output-escaping="yes" select="content"/>
	</xsl:template>

	<xsl:template match="part" mode="title">
		<strong><xsl:value-of select="language"/></strong>
		<xsl:text> expression </xsl:text>
		"<strong><xsl:value-of select="expression"/></strong>"
		<xsl:text> on </xsl:text>
		"<strong><xsl:value-of select="url"/></strong>"
	</xsl:template>

</xsl:stylesheet>
