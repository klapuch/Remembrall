<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="assets.xsl"/>

	<xsl:template name="meta">
		<xsl:param name="description"/>
		<xsl:if test="$description != ''">
			<meta name="description" content="{substring($description, 1, 150)}"/>
		</xsl:if>
		<meta name="robots" content="index, follow"/>
		<meta name="author" content="Dominik Klapuch"/>
	</xsl:template>

</xsl:stylesheet>
