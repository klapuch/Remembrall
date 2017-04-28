<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template name="markup-modal">
		<xsl:param name="id"/>
		<xsl:param name="markup"/>
		<div class="modal fade" id="markup-{$id}" tabindex="-1" role="dialog" aria-labelledby="markup-label-{$id}">
			<div class="modal-dialog modal-xl" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true"/>
						</button>
						<h4 class="modal-title" id="markup-label-{$id}">
							Content
						</h4>
					</div>
					<div class="modal-body">
						<xsl:value-of disable-output-escaping="yes" select="$markup"/>
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
