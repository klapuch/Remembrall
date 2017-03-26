<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../templates/@layout.xsl"/>
	<xsl:import href="../../Parts/components/content-modal.xsl"/>
	<xsl:import href="../../components/direction.xsl"/>
	<xsl:import href="../../components/form.xsl"/>

	<xsl:template match="page">
		<h1><xsl:apply-templates select="body/header[@level=1]"/></h1>
		<xsl:apply-templates select="subscriptions"/>
	</xsl:template>

	<xsl:template match="subscriptions"> <table class="table table-hover">
			<xsl:apply-templates select="/page/body/tables/table[@purpose='overview']"/>
			<tbody>
				<xsl:apply-templates select="subscription"/>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="subscription">
		<tr>
			<td><xsl:number format="1. "/></td>
			<td><xsl:value-of select="last_update"/></td>
			<td><xsl:value-of select="interval"/></td>
			<td><xsl:value-of select="expression"/></td>
			<td><xsl:value-of select="url"/></td>
			<td>
				<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#content-{id}">
					<span class="glyphicon glyphicon-eye-open" aria-hidden="true"/>
				</button>
				<xsl:call-template name="modal">
					<xsl:with-param name="id" select="id"/>
					<xsl:with-param name="content" select="content"/>
				</xsl:call-template>
			</td>
			<td>
				<xsl:apply-templates select="/page/body/confirmations">
					<xsl:with-param name="id" select="id"/>
				</xsl:apply-templates>
				<a href="{$base_url}/subscription/edit/{id}" class="btn btn-primary btn-sm" role="button" title="Edit">
					<span class="glyphicon glyphicon-edit" aria-hidden="true"/>
				</a>
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

	<xsl:template match="confirmation[@purpose='cancellation']">
		<xsl:param name="id"/>
		<xsl:apply-templates select="/page/forms/form[@name=concat('delete-', $id)]" mode="delete">
			<xsl:with-param name="title" select="title"/>
			<xsl:with-param name="message" select="message"/>
		</xsl:apply-templates>
	</xsl:template>

</xsl:stylesheet>
