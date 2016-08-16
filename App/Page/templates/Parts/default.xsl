<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="../@layout.xsl"/>
    <xsl:param name="default" select="document('default.xml')/default"/>
	<xsl:param name="title" select="$default/title"/>
	<xsl:param name="description" select="$default/description"/>
	<xsl:template match="/subscriptions">
		<table class="table table-hover">
			<thead>
				<tr>
					<xsl:apply-templates select="tables/subscribing"/>
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
                <td><xsl:apply-templates select="$default/confirmations"/></td>
			</tr>
		 </xsl:for-each>
	</xsl:template>
    <xsl:template match="subscribing">
        <xsl:apply-templates/>
    </xsl:template>
	<xsl:template match="number|visitation|interval|expression|url|options">
        <th><p><xsl:value-of select="."/></p></th>
	</xsl:template>
    <xsl:template match="cancel">
        <a role="button" href="{href}" onclick="return confirm ('{message}')" title="{title}" type="button" class="btn btn-danger btn-sm">
            <span class="glyphicon glyphicon-{glyphicon}" aria-hidden="true"/>
        </a>
    </xsl:template>
</xsl:stylesheet>
