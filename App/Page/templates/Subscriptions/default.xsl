<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../@layout.xsl"/>

	<xsl:template match="body">
		<xsl:apply-templates select="subscriptions"/>
	</xsl:template>

	<xsl:template match="subscriptions">
		<table class="table table-hover">
			<xsl:apply-templates select="/body/tables/overview/headings"/>
			<tbody>
				<xsl:apply-templates select="subscription">
					<xsl:sort select="lastUpdate" order="descending"/>
				</xsl:apply-templates>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="subscription">
		<tr>
			<td><xsl:number format="1. "/></td>
			<td><xsl:value-of select="lastUpdate"/></td>
			<td><xsl:value-of select="interval"/></td>
			<td><xsl:value-of select="expression"/></td>
			<td><xsl:value-of select="url"/></td>
			<td>
				<xsl:apply-templates select="/body/confirmations/cancel">
					<xsl:with-param name="id" select="id"/>
				</xsl:apply-templates>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="headings">
		<thead><tr><xsl:apply-templates/></tr></thead>
	</xsl:template>

	<xsl:template match="heading">
		<th><p><xsl:value-of select="."/></p></th>
	</xsl:template>

	<xsl:template match="confirmations/*">
		<xsl:param name="id"/>
		<a
			role="button"
			href="{concat(//baseUrl, concat(concat(concat(href, concat('?id=', $id)), '&amp;'), //csrf/link))}"
			onclick="return confirm('{normalize-space(message)}')"
			title="{title}" type="button" class="btn btn-danger btn-sm">
			<span class="glyphicon glyphicon-{glyphicon}" aria-hidden="true"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
