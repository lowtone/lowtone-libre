<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="posts.xsl" />
	<xsl:import href="pagination.xsl" />


	<!-- Query -->

	<xsl:template match="query">
		<xsl:apply-templates select="posts" />
		<xsl:apply-templates select="pagination" />
	</xsl:template>

</xsl:stylesheet>