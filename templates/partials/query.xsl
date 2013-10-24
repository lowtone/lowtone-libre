<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012-2013, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="posts.xsl" />
	<xsl:import href="pagination.xsl" />


	<!-- Query -->

	<xsl:template match="query">

		<!-- Posts -->

		<xsl:call-template name="before_posts" />
		<xsl:apply-templates select="posts" />
		<xsl:call-template name="after_posts" />

		<!-- Pagination -->

		<xsl:call-template name="before_pagination" />
		<xsl:apply-templates select="pagination" />
		<xsl:call-template name="after_pagination" />
	</xsl:template>

	<xsl:template name="before_posts">
		<xsl:if test="@archive">
			<header>
				<h1><xsl:value-of select="posts/locales/title" /></h1>
			</header>
		</xsl:if>
	</xsl:template>

	<xsl:template name="after_posts" />

	<xsl:template name="before_pagination" />

	<xsl:template name="after_pagination" />

</xsl:stylesheet>