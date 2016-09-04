<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl">
	<xsl:import href="@headers.xsl"/>

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template name="additionalStyles"/>
	<xsl:template name="additionalScripts"/>

	<xsl:template match="/">
		<html lang="cs-cz">
			<head>
				<title>
					<xsl:value-of select="normalize-space($title)"/>
				</title>
				<xsl:call-template name="meta"/>
				<xsl:call-template name="styles"/>
				<xsl:call-template name="additionalStyles"/>
			</head>
			<body>
				<div id="wrap">
					<nav class="navbar navbar-default navbar-static-top">
						<div class="container">
							<div class="navbar-header">
								<button type="button"
										class="navbar-toggle collapsed"
										data-toggle="collapse"
										data-target="#navbar"
										aria-expanded="false"
										aria-controls="navbar">
									<span class="sr-only">Toggle navigation
									</span>
									<span class="icon-bar"/>
									<span class="icon-bar"/>
									<span class="icon-bar"/>
                                </button>
                                <xsl:element name="a">
                                    <xsl:attribute name="href">
                                        <xsl:value-of select="$baseUrl"/>
                                    </xsl:attribute>
                                    <xsl:attribute name="class">
                                        navbar-brand
                                    </xsl:attribute>
                                    <xsl:attribute name="title">
                                        Remembrall
                                    </xsl:attribute>
                                    <strong>Remembrall</strong>
                                </xsl:element>
							</div>
							<div id="navbar" class="navbar-collapse collapse">
								<ul class="nav navbar-nav">
                                    <li>
                                        <xsl:element name="a">
                                            <xsl:attribute name="href">
                                                <xsl:value-of select="concat($baseUrl, 'parts/')"/>
                                            </xsl:attribute>
                                            Parts
                                        </xsl:element>
									</li>
                                    <li>
                                        <xsl:element name="a">
                                            <xsl:attribute name="href">
                                                <xsl:value-of select="concat($baseUrl, 'subscription/')"/>
                                            </xsl:attribute>
                                            Subscription
                                        </xsl:element>
									</li>
								</ul>
							</div>
						</div>
					</nav>
					<div class="container">
						<xsl:apply-templates/>
					</div>
				</div>
				<div id="footer">
					<div class="container">
						<p class="muted credit text-center">
							<a href="https://www.github.com/klapuch"
							   class="no-link"
							   target="_blank">
								Created with
								<span id="heart">❤</span>
							</a>
						</p>
					</div>
				</div>
				<xsl:call-template name="scripts"/>
				<xsl:call-template name="additionalScripts"/>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>
