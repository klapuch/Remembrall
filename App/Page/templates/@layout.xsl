<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../components/head.xsl"/>
	<xsl:import href="../components/assets.xsl"/>

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
						<xsl:with-param name="user" select="page/user"/>
					</xsl:apply-templates>
					<div class="container">
						<xsl:apply-templates select="page/flashMessages/flashMessage"/>
						<xsl:apply-templates/>
					</div>
				</div>
				<xsl:call-template name="footer"/>
				<xsl:apply-templates select="page/body/assets"/>
				<script>hljs.initHighlightingOnLoad();</script>
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
					<xsl:call-template name="logo"/>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<xsl:apply-templates select="key('linkByHref', key('permissionByRole', $user/@role)/@resource)/..">
							<xsl:with-param name="user" select="$user"/>
						</xsl:apply-templates>
					</ul>
				</div>
			</div>
		</nav>
	</xsl:template>

	<xsl:template name="logo">
		<a href="{$base_url}" class="navbar-brand" title="Remembrall">
			<strong>Remembrall</strong>
		</a>
	</xsl:template>

	<xsl:template match="item">
		<xsl:param name="user"/>
		<li>
			<xsl:apply-templates/>
		</li>
	</xsl:template>

	<xsl:template match="link">
		<a href="{concat($base_url, @href)}" title="{.}">
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
