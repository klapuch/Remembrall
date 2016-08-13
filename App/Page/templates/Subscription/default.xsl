<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:param name="title" select="'Subscription'"/>
    <xsl:param name="description" select="'Subscribe a new part'"/>
    <xsl:include href="../@layout.xsl"/>
    <xsl:template match="//form">
        <xsl:param name="url" select="placeholder/url"/>
        <xsl:param name="expression" select="placeholder/expression"/>
        <xsl:param name="interval" select="placeholder/interval"/>
        <xsl:param name="act" select="submit"/>
        <form class="form-horizontal" role="form" method="POST">
            <div class="form-group">
                <div class="col-sm-5">
                    <label>URL</label>
                    <input type="text" required="required" name="url" class="form-control" placeholder="{$url}"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <label>XPath expression</label>
                    <input type="text" required="required" name="expression" class="form-control" placeholder="{$expression}"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <label>Interval</label>
                    <input type="number" required="required" min="30" name="interval" class="form-control" placeholder="{$interval}"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <input type="submit" name="act" class="form-control" value="{$act}"/>
                </div>
            </div>
        </form>
    </xsl:template>
    <xsl:template name="additionalScripts"/>
    <xsl:template name="additionalStyles"/>
</xsl:stylesheet>
