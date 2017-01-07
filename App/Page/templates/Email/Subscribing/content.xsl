<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="part">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="cs-cz">
			<body>
				<xsl:element name="p">
					<xsl:text>Hi, there are some changes on </xsl:text>
					<xsl:value-of select="url"/>
					<xsl:text> website with </xsl:text>
					<xsl:value-of select="expression"/>
					<xsl:text> expression</xsl:text>
				</xsl:element>

				<p>Check it out bellow this text</p>
				<br/>
				<p><xsl:value-of select="content"/></p>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
