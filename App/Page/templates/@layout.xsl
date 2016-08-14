<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:import href="@headers.xsl"/>
    <xsl:output method="html" encoding="utf-8"/>
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
                                <a class="navbar-brand" href="/"
                                   title="Remembrall">
                                    <strong>Remembrall</strong>
                                </a>
                            </div>
                            <div id="navbar" class="navbar-collapse collapse">
                                <ul class="nav navbar-nav">
                                    <li>
                                        <a href="/parts">Parts</a>
                                    </li>
                                    <li>
                                        <a href="/subscription">
                                            Subscription
                                        </a>
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