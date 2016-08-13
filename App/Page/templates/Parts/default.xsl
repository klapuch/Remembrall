<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:php="http://php.net/xsl" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:param name="title" select="'Parts'"/>
    <xsl:param name="description" select="'All the parts owned by you'"/>
    <xsl:include href="../@layout.xsl"/>
    <xsl:template match="/subscriptions">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><p>#</p></th>
                    <th><p>Last visitation</p></th>
                    <th><p>Interval</p></th>
                    <th><p>Expression</p></th>
                    <th><p>Page</p></th>
                    <th><p>Options</p></th>
                </tr>
            </thead>
            <tbody>
                <xsl:for-each select="subscription">
                    <tr>
                        <td><xsl:number value="position()" format="1."/></td>
                        <td><xsl:value-of select="visitation"/></td>
                        <td><xsl:value-of select="interval"/></td>
                        <td><xsl:value-of select="expression"/></td>
                        <td><xsl:value-of select="url"/></td>
                        <td>
                             <a role="button" href="" onclick="return confirm('Are you sure you want to cancel the subscription?')" title="Remove" type="button" class="btn btn-danger btn-sm">
                               <span class="glyphicon glyphicon-remove" aria-hidden="true"/>
                             </a>
                        </td>
                    </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
    <xsl:template name="additionalScripts"/>
    <xsl:template name="additionalStyles"/>
</xsl:stylesheet>
