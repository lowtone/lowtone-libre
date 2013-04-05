<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="widgets.xsl" />
	
	<!-- Sidebar -->
	
	<xsl:template match="sidebar">
		<xsl:param name="width">one-third</xsl:param>
		
		<aside id="{@id}" class="{$width} column">
			<xsl:apply-templates select="widgets/widget" />
		</aside>
	</xsl:template>

</xsl:stylesheet>