<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../templates/@layout.xsl"/>
	<xsl:import href="tabs.xsl"/>
	<xsl:import href="../components/markup-modal.xsl"/>
	<xsl:import href="../../components/direction.xsl"/>
	<xsl:import href="../../components/pager.xsl"/>
	<xsl:import href="../../components/per_page_select.xsl"/>

	<xsl:template match="page">
		<xsl:apply-templates select="body/tabs" mode="parts"/>
		<h1><xsl:apply-templates select="body/header[@level=1]"/></h1>
		<xsl:apply-templates select="body/selects/select[@purpose='pagination']" mode="pagination">
			<xsl:with-param name="per_page" select="request/get/per_page"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="parts"/>
		<xsl:apply-templates select="pagination">
			<xsl:with-param name="per_page" select="request/get/per_page"/>
		</xsl:apply-templates>
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
			<td>
				<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#markup-{id}">
					<span class="glyphicon glyphicon-eye-open" aria-hidden="true"/>
				</button>
				<xsl:call-template name="markup-modal">
					<xsl:with-param name="id" select="id"/>
					<xsl:with-param name="markup" select="content"/>
				</xsl:call-template>
			</td>
			<td><xsl:value-of select="occurrences"/></td>
			<td>
				<xsl:apply-templates select="/page/body/options">
					<xsl:with-param name="url" select="url"/>
					<xsl:with-param name="expression" select="expression"/>
				</xsl:apply-templates>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="headings">
		<thead><tr><xsl:apply-templates/></tr></thead>
	</xsl:template>

	<xsl:template match="heading">
		<th>
			<xsl:call-template name="direction">
				<xsl:with-param name="sort" select="@sort"/>
				<xsl:with-param name="current" select="/page/request/get/sort"/>
			</xsl:call-template>
		</th>
	</xsl:template>

	<xsl:template match="option[@purpose='acquire']">
		<xsl:param name="url"/>
		<xsl:param name="expression"/>
		<a href="{$base_url}/subscription?url={$url}&amp;expression={$expression}" class="btn btn-primary btn-sm" role="button" title="{title}">
			<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
