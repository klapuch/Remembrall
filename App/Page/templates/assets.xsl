<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:param name="assets" select="document('assets.xml')/assets"/>

    <xsl:template name="styles">
        <xsl:apply-templates select="$assets/styles/style"/>
    </xsl:template>

	<xsl:template name="scripts">
		<xsl:apply-templates select="$assets/scripts/script"/>
	</xsl:template>

    <xsl:template match="style">
        <xsl:element name="link">
            <xsl:attribute name="rel">stylesheet</xsl:attribute>
            <xsl:attribute name="href">
				<xsl:apply-templates select="href" mode="assets"/>
            </xsl:attribute>
            <xsl:if test="integrity">
                <xsl:attribute name="integrity">
					<xsl:apply-templates select="integrity" mode="assets"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="crossorigin">
				<xsl:attribute name="crossorigin">
					<xsl:apply-templates select="crossorigin" mode="assets"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
    </xsl:template>

    <xsl:template match="script">
        <xsl:element name="script">
            <xsl:attribute name="src">
				<xsl:apply-templates select="src" mode="assets"/>
            </xsl:attribute>
            <xsl:if test="integrity">
				<xsl:attribute name="integrity">
					<xsl:apply-templates select="integrity" mode="assets"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:if test="crossorigin">
                <xsl:attribute name="crossorigin">
					<xsl:apply-templates select="crossorigin" mode="assets"/>
                </xsl:attribute>
            </xsl:if>
        </xsl:element>
	</xsl:template>

	<xsl:template match="text()" mode="assets">
		<xsl:value-of select="normalize-space(.)"/>
	</xsl:template>

</xsl:stylesheet>
