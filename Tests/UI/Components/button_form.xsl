<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="../../../App/Page/components/form.xsl"/>

	<xsl:template match="/">
        <xsl:apply-templates select="form" mode="button">
            <xsl:with-param name="title" select="$title"/>
            <xsl:with-param name="value" select="$value"/>
            <xsl:with-param name="class" select="$class"/>
        </xsl:apply-templates>
	</xsl:template>

</xsl:stylesheet>
