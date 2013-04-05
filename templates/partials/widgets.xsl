<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	
	<!-- Widget -->
	
	<xsl:template match="widget">
		<!-- 
			Widget output is supposed to use before_widget and after_widget and
			therefore doesn't have a container element.
		 -->
		<xsl:value-of select="output" disable-output-escaping="yes" />
	</xsl:template>

</xsl:stylesheet>