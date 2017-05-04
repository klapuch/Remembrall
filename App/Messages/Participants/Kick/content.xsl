<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="invitation">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="cs-cz">
			<body>
				<xsl:text>You have been kicked from subscription.</xsl:text>
				<br/>
				<xsl:text>Details:</xsl:text>
				<br/>
				<ul>
					<li>URL: <xsl:value-of select="url"/></li>
					<li>Expression: <xsl:value-of select="expression"/></li>
					<li>Author: <xsl:value-of select="author"/></li>
				</ul>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
