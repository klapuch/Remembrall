<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../../App/Page/components/per_page_select.xsl"/>

	<xsl:template match="/">
		<xsl:apply-templates mode="pagination">
			<xsl:with-param name="per_page" select="$per_page"/>
		</xsl:apply-templates>
	</xsl:template>

</xsl:stylesheet>
