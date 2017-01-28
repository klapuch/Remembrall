<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../templates/@layout.xsl"/>
	<xsl:import href="tabs.xsl"/>

	<xsl:template match="page">
		<xsl:apply-templates select="body/tabs" mode="parts">
			<xsl:with-param name="baseUrl" select="baseUrl"/>
		</xsl:apply-templates>
		<h1><xsl:apply-templates select="body/header[@level = 1]"/></h1>
		<xsl:apply-templates select="parts"/>
	</xsl:template>

	<xsl:template match="parts">
		<table class="table table-hover">
			<xsl:apply-templates select="/page/body/tables/table[@purpose='overview']"/>
			<tbody>
				<xsl:apply-templates select="part"/>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="part">
		<tr>
			<td><xsl:number format="1. "/></td>
			<td><xsl:value-of select="url"/></td>
			<td><xsl:value-of select="expression"/></td>
			<td><xsl:value-of select="content"/></td>
			<td><xsl:value-of select="occurrences"/></td>
		</tr>
	</xsl:template>

	<xsl:template match="headings">
		<thead><tr><xsl:apply-templates/></tr></thead>
	</xsl:template>

	<xsl:template match="heading">
		<th><p><xsl:apply-templates/></p></th>
	</xsl:template>

</xsl:stylesheet>
