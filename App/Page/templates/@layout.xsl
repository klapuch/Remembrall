<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="head.xsl"/>

	<xsl:output method="html" encoding="utf-8"/>

	<!-- To be overridden -->
	<xsl:template name="additionalScripts"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="cs-cz">
			<head>
				<xsl:apply-templates select="body/head" mode="layout"/>
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
									<span class="sr-only">Toggle navigation</span>
									<span class="icon-bar"/>
									<span class="icon-bar"/>
									<span class="icon-bar"/>
								</button>
								<xsl:call-template name="logo">
									<xsl:with-param name="baseUrl" select="body/baseUrl"/>
								</xsl:call-template>
							</div>
							<div id="navbar" class="navbar-collapse collapse">
								<xsl:call-template name="link-bar">
									<xsl:with-param name="baseUrl" select="body/baseUrl"/>
								</xsl:call-template>
							</div>
						</div>
					</nav>
					<div class="container">
						<xsl:apply-templates select="body/flashMessages/flashMessage"/>
						<xsl:apply-templates/>
					</div>
				</div>
				<xsl:call-template name="footer"/>
				<xsl:call-template name="scripts"/>
				<xsl:call-template name="additionalScripts"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="head" mode="layout">
		<xsl:call-template name="head">
			<xsl:with-param name="description">
				<xsl:apply-templates select="description"/>
			</xsl:with-param>
			<xsl:with-param name="title">
				<xsl:apply-templates select="title"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>


	<xsl:template name="footer">
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
	</xsl:template>

	<xsl:template name="logo">
		<xsl:param name="baseUrl"/>
		<a href="{$baseUrl}" class="navbar-brand" title="Remembrall">
			<strong>Remembrall</strong>
		</a>
	</xsl:template>

	<xsl:template name="link-bar">
		<xsl:param name="baseUrl"/>
		<ul class="nav navbar-nav">
			<xsl:for-each select="document('links.xml')/links/link">
				<li>
					<xsl:call-template name="links">
						<xsl:with-param name="href" select="@href"/>
						<xsl:with-param name="title" select="."/>
						<xsl:with-param name="baseUrl" select="$baseUrl"/>
					</xsl:call-template>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>

	<xsl:template name="links">
		<xsl:param name="href"/>
		<xsl:param name="title"/>
		<xsl:param name="baseUrl"/>
		<a href="{concat($baseUrl, $href)}" title="{$title}">
			<xsl:value-of select="$title"/>
		</a>
	</xsl:template>

	<xsl:template match="flashMessage">
		<xsl:if test="boolean(content) and boolean(type)">
			<xsl:element name="div">
				<xsl:attribute name="class">
					<xsl:text>alert alert-</xsl:text>
					<xsl:value-of select="type"/>
				</xsl:attribute>
				<xsl:value-of select="content"/>
			</xsl:element>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
