<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="pagination">
		<xsl:param name="per_page"/>
		<nav aria-label="pager">
			<xsl:if test="first!=last">
				<ul class="pager">
					<xsl:if test="first!=current">
						<li class="previous">
							<xsl:call-template name="link" mode="pager">
								<xsl:with-param name="per_page" select="$per_page"/>
								<xsl:with-param name="step" select="previous"/>
								<xsl:with-param name="content">
									<span aria-hidden="true">←</span>
									Previous
								</xsl:with-param>
							</xsl:call-template>
						</li>
					</xsl:if>
					<xsl:if test="last!=current">
						<li class="next">
							<xsl:call-template name="link" mode="pager">
								<xsl:with-param name="per_page" select="$per_page"/>
								<xsl:with-param name="step" select="next"/>
								<xsl:with-param name="content">
									Next
									<span aria-hidden="true">→</span>
								</xsl:with-param>
							</xsl:call-template>
						</li>
					</xsl:if>
				</ul>
			</xsl:if>
		</nav>
	</xsl:template>

	<xsl:template name="link" mode="pager">
		<xsl:param name="step"/>
		<xsl:param name="content"/>
		<xsl:param name="per_page"/>
		<xsl:element name="a">
			<xsl:attribute name="href">
				<xsl:text>?page=</xsl:text>
				<xsl:value-of select="$step"/>
				<xsl:if test="$per_page">
					<xsl:text>&amp;per_page=</xsl:text>
					<xsl:value-of select="$per_page"/>
				</xsl:if>
			</xsl:attribute>
			<xsl:copy-of select="$content"/>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
