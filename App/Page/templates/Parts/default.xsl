<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../@layout.xsl"/>

	<xsl:param name="title" select="template/default/title"/>
	<xsl:param name="description" select="template/default/description"/>

	<xsl:template match="template">
		<xsl:apply-templates select="subscriptions"/>
	</xsl:template>

	<xsl:template match="subscriptions">
		<table class="table table-hover">
			<xsl:apply-templates select="//subscription/headings"/>
			<tbody>
                <xsl:apply-templates select="subscription">
                    <xsl:sort select="lastUpdate" order="descending"/>
                </xsl:apply-templates>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="subscription">
		<tr>
			<td><xsl:number value="position()" format="1. "/></td>
			<td><xsl:value-of select="lastUpdate"/></td>
			<td><xsl:value-of select="interval"/></td>
			<td><xsl:value-of select="expression"/></td>
			<td><xsl:value-of select="url"/></td>
			<td><xsl:apply-templates select="//confirmation"/></td>
		</tr>
	</xsl:template>

	<xsl:template match="headings">
		<thead>
			<tr>
				<xsl:apply-templates/>
			</tr>
		</thead>
	</xsl:template>

	<xsl:template match="heading">
		<th><p><xsl:value-of select="."/></p></th>
	</xsl:template>

    <xsl:template match="confirmation">
        <a role="button" href="{href}" onclick="return confirm ('{message}')"
                 title="{title}" type="button" class="btn btn-danger btn-sm">
			<span class="glyphicon glyphicon-{glyphicon}" aria-hidden="true"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
