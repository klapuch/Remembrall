<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../@layout.xsl"/>

	<xsl:param name="title" select="template/default/title"/>
	<xsl:param name="description" select="template/default/description"/>

	<xsl:template match="default">
		<xsl:apply-templates select="forms"/>
	</xsl:template>

	<xsl:template match="forms">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="subscribing">
		<form class="form-horizontal" role="form" method="POST">
			<div class="form-group">
				<div class="col-sm-5">
					<label><xsl:value-of select="label/url"/></label>
					<input type="text" required="required" name="url" class="form-control" placeholder="{placeholder/url}"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-5">
					<label><xsl:value-of select="label/expression"/></label>
					<input type="text" required="required" name="expression" class="form-control" placeholder="{placeholder/expression}"/>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-5">
					<label><xsl:value-of select="label/interval"/></label>
					<input type="number" required="required" min="30" name="interval" class="form-control" placeholder="{placeholder/interval}"/>
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
