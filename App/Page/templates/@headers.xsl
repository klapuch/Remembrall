<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template name="meta">
        <meta name="description" content="{substring(normalize-space($description), 1, 150)}"/>
        <meta name="robots" content="index, follow"/>
        <meta name="author" content="Dominik Klapuch"/>
    </xsl:template>
    <xsl:template name="styles">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"/>
    </xsl:template>
    <xsl:template name="scripts">
        <script src="https://code.jquery.com/jquery-3.1.0.min.js"/>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"/>
    </xsl:template>
</xsl:stylesheet>