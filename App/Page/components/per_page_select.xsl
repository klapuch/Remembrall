<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

	<xsl:template match="select" mode="pagination">
		<xsl:param name="per_page"/>
		<div class="row">
			<div class="col-xs-2 col-xs-offset-10">
				<select id="per_page" class="form-control">
					<xsl:apply-templates mode="pagination">
						<xsl:with-param name="per_page" select="$per_page"/>
					</xsl:apply-templates>
				</select>
			</div>
		</div>
	</xsl:template>

	<xsl:template match="option" mode="pagination">
		<xsl:param name="per_page"/>
		<xsl:variable name="query">
			<xsl:text>page=1</xsl:text>
			<xsl:text>&amp;</xsl:text>
			<xsl:text>per_page=</xsl:text>
			<xsl:value-of select="."/>
		</xsl:variable>
		<xsl:element name="option">
			<xsl:attribute name="value">
				<xsl:value-of select="php:function('target', $query)"/>
			</xsl:attribute>
			<xsl:if test="$per_page=.">
				<xsl:attribute name="selected">true</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="@label"/>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
