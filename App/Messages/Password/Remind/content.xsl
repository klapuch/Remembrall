<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="remind">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="cs-cz">
			<body>
				<xsl:text>Your password has been reset.</xsl:text>
                <br/>
				<xsl:text>To change your password follow the link bellow.</xsl:text>
				<br/>
				<xsl:element name="a">
					<xsl:attribute name="href">
						<xsl:value-of select="base_url"/>
						<xsl:text>/password/reset/</xsl:text>
						<xsl:value-of select="$reminder"/>
					</xsl:attribute>
					<xsl:value-of select="$reminder"/>
				</xsl:element>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
