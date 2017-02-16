<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template name="direction">
		<xsl:param name="sort"/>
		<xsl:param name="current"/>
		<xsl:choose>
			<xsl:when test="$sort">
				<xsl:choose>
					<xsl:when test="$current=$sort">
						<a href="?sort=-{$sort}">
							<xsl:apply-templates mode="selection">
								<xsl:with-param name="direction" select="'top'"/>
								<xsl:with-param name="current" select="$current"/>
								<xsl:with-param name="sort" select="$sort"/>
							</xsl:apply-templates>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="?sort={$sort}">
							<xsl:apply-templates mode="selection">
								<xsl:with-param name="direction" select="'bottom'"/>
								<xsl:with-param name="current" select="$current"/>
								<xsl:with-param name="sort" select="$sort"/>
							</xsl:apply-templates>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates mode="selection"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="node()" mode="selection">
		<xsl:param name="direction"/>
		<xsl:param name="current"/>
		<xsl:param name="sort"/>
		<p>
			<xsl:value-of select="."/>
			<xsl:if test="$direction and $current and ($sort = $current or concat('-', $sort) = $current)">
				<span class="glyphicon glyphicon-triangle-{$direction}" aria-hidden="true"/>
			</xsl:if>
		</p>
	</xsl:template>

</xsl:stylesheet>
