<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:import href="../@layout.xsl"/>

    <xsl:param name="title" select="template/default/title"/>
    <xsl:param name="description" select="template/default/description"/>

    <xsl:template match="default">
        <xsl:apply-templates select="forms"/>
    </xsl:template>

    <xsl:template match="forms">
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="in">
        <form action="access" class="form-horizontal" role="form" method="POST">
            <div class="form-group">
                <div class="col-sm-5">
                    <label>
                        <xsl:value-of select="label/email"/>
                    </label>
                    <input type="email" required="required" name="email" class="form-control" />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <label>
                        <xsl:value-of select="label/password"/>
                    </label>
                    <input type="password" required="required" name="password" class="form-control" />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <input type="submit" name="act" class="form-control" value="{submit/act}"/>
                </div>
            </div>
        </form>
    </xsl:template>

</xsl:stylesheet>
