<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:param name="default" select="document('default.xml')/default"/>
	<xsl:param name="title" select="$default/title"/>
	<xsl:param name="description" select="$default/description"/>
	<xsl:include href="../@layout.xsl"/>
	<xsl:template match="/subscriptions">
		<table class="table table-hover">
			<thead>
				<tr>
					<xsl:apply-templates select="document('headings.xml')/headings"/>
				</tr>
			</thead>
			<tbody>
				<xsl:call-template name="rows"/>
			</tbody>
		</table>
	</xsl:template>
	<xsl:template name="rows">
		 <xsl:for-each select="subscription">
			 <xsl:sort select="visitation" order="descending"/>
			<tr>
				<td><xsl:number value="position()" format="1."/></td>
				<td><xsl:value-of select="visitation"/></td>
				<td><xsl:value-of select="interval"/></td>
				<td><xsl:value-of select="expression"/></td>
				<td><xsl:value-of select="url"/></td>
				<td>
					<a role="button" href="" onclick="return confirm('{$default/cancelConfirmation/message}')" title="{$default/cancelConfirmation/title}" type="button" class="btn btn-danger btn-sm">
						<span class="glyphicon glyphicon-remove" aria-hidden="true"/>
					</a>
				</td>
			</tr>
		 </xsl:for-each>
	</xsl:template>
	<xsl:template match="headings">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="number|visitation|interval|expression|url|options">
        <th><p><xsl:value-of select="."/></p></th>
	</xsl:template>
	<xsl:template name="additionalScripts"/>
	<xsl:template name="additionalStyles"/>
</xsl:stylesheet>
