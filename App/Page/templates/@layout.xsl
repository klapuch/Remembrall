<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:import href="head.xsl"/>
	<xsl:import href="assets.xsl"/>

	<xsl:key name="permissionByRole" match="permission" use="@role"/>
	<xsl:key name="linkByHref" match="link" use="@href"/>

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="cs-cz">
			<xsl:apply-templates select="page/head" mode="layout"/>
			<body>
				<div id="wrap">
					<xsl:apply-templates select="page/body/menu[@name='main']">
						<xsl:with-param name="baseUrl" select="page/baseUrl"/>
						<xsl:with-param name="user" select="page/user"/>
					</xsl:apply-templates>
					<div class="container">
						<xsl:apply-templates select="page/flashMessages/flashMessage"/>
						<xsl:apply-templates/>
					</div>
				</div>
				<xsl:call-template name="footer"/>
				<xsl:apply-templates select="page/body/assets"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="head" mode="layout">
		<head>
			<xsl:apply-templates/>
			<xsl:apply-templates select="page/head"/>
		</head>
	</xsl:template>

	<xsl:template match="menu[@name='main']">
		<xsl:param name="baseUrl"/>
		<xsl:param name="user"/>
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
						<xsl:with-param name="baseUrl" select="$baseUrl"/>
					</xsl:call-template>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<xsl:apply-templates select="key('linkByHref', key('permissionByRole', $user/@role)/@href)/..">
							<xsl:with-param name="baseUrl" select="$baseUrl"/>
							<xsl:with-param name="user" select="$user"/>
						</xsl:apply-templates>
					</ul>
				</div>
			</div>
		</nav>
	</xsl:template>

	<xsl:template name="logo">
		<xsl:param name="baseUrl"/>
		<a href="{$baseUrl}" class="navbar-brand" title="Remembrall">
			<strong>Remembrall</strong>
		</a>
	</xsl:template>

	<xsl:template match="item">
		<xsl:param name="baseUrl"/>
		<xsl:param name="user"/>
		<li>
			<xsl:apply-templates>
				<xsl:with-param name="baseUrl" select="$baseUrl"/>
			</xsl:apply-templates>
		</li>
	</xsl:template>

	<xsl:template match="link">
		<xsl:param name="baseUrl"/>
		<a href="{concat($baseUrl, @href)}" title="{.}">
			<xsl:value-of select="."/>
		</a>
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
