<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template name="modal" mode="content">
		<xsl:param name="id"/>
		<xsl:param name="content"/>
		<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#content-{$id}">
			<span class="glyphicon glyphicon-eye-open" aria-hidden="true"/>
		</button>
		<div class="modal fade" id="content-{$id}" tabindex="-1" role="dialog" aria-labelledby="content-label-{$id}">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true"/>
						</button>
						<h4 class="modal-title" id="content-label-{$id}">
							Content
						</h4>
					</div>
					<div class="modal-body">
						<xsl:value-of disable-output-escaping="yes" select="$content"/>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">
							Close
						</button>
					</div>
				</div>
			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>
