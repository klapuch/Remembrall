<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../App/Page/templates/direction.xsl"/>

	<xsl:template match="/">
		<xsl:call-template name="direction">
			<xsl:with-param name="sort" select="$sort"/>
			<xsl:with-param name="current" select="$current"/>
		</xsl:call-template>
	</xsl:template>

</xsl:stylesheet>
