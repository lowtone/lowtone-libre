<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012, Paul van der Meijs
	@version 1.0
 -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:import href="taxonomies.xsl" />
	<xsl:import href="attachments.xsl" />
	<xsl:import href="meta.xsl" />
	<xsl:import href="comments.xsl" />
	
	<!-- Posts -->
	
	<xsl:template match="posts">
		<xsl:variable name="hasPosts" select="boolean(count(post))" />
		
		<div>
			<xsl:attribute name="class">
				<xsl:text>posts</xsl:text>
				<xsl:if test="not($hasPosts)">
					<xsl:text> empty</xsl:text>
				</xsl:if>
			</xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="$hasPosts">
					<xsl:variable name="single" select="boolean(//query/@single)" />

					<xsl:choose>
						<xsl:when test="$single">
							<xsl:apply-templates select="post" mode="single" />
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates select="post" />
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<p class="no-items"><xsl:value-of select="locales/no_posts" /></p>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	</xsl:template>
	
	
	<!-- Post -->
	
	<xsl:template match="post">
		<xsl:param name="single" />
		
		<article id="{name}" data-id="{@id}" itemscope="itemscope" itemtype="http://schema.org/Article">
			<xsl:call-template name="post_class">
				<xsl:with-param name="single"><xsl:value-of select="$single" /></xsl:with-param>
			</xsl:call-template>
			<xsl:apply-templates select="thumbnail">
				<xsl:with-param name="link">
					<xsl:if test="not(boolean($single))">
						<xsl:value-of select="permalink" />
					</xsl:if>
			</xsl:with-param>
			</xsl:apply-templates>
			<header>
				<h1>
					<xsl:choose>
						<xsl:when test="$single">
							<span itemprop="name"><xsl:value-of select="title" disable-output-escaping="yes" /></span>
						</xsl:when>
						<xsl:otherwise>
							<a href="{permalink}"><span itemprop="name"><xsl:value-of select="title" disable-output-escaping="yes" /></span></a>
						</xsl:otherwise>
					</xsl:choose>
				</h1>
				<div class="info">
					<xsl:call-template name="post_date" />
					<xsl:apply-templates select="taxonomies" />
					<xsl:call-template name="post_author" />
				</div>
			</header>
			<div class="content">
				<xsl:value-of select="content" disable-output-escaping="yes" />
			</div>
			<xsl:apply-templates select="custom_fields" />
			<xsl:apply-templates select="adjacent" />
			<xsl:call-template name="comments" />
		</article>
	</xsl:template>
	
	
	<!-- Single post -->

	<xsl:template match="post" mode="single">
		<xsl:apply-templates select=".">
			<xsl:with-param name="single" select="true()" />
		</xsl:apply-templates>
	</xsl:template>


	<!-- Post class -->

	<xsl:template name="post_class">
		<xsl:param name="single" />

		<xsl:attribute name="class">
			<xsl:text>post type-</xsl:text><xsl:value-of select="type" />
			<xsl:for-each select=".//term">
				<xsl:text> </xsl:text><xsl:value-of select="taxonomy" />-<xsl:value-of select="slug" />
			</xsl:for-each>
			<xsl:if test="$single">
				<xsl:text> single</xsl:text>
			</xsl:if>
		</xsl:attribute>
	</xsl:template>


	<!-- Post date -->

	<xsl:template name="post_date">
		<dl class="date">
			<dt><xsl:value-of select="locales/date_title" /></dt>
			<dd><xsl:value-of select="date" /></dd>
		</dl>
	</xsl:template>


	<!-- Post author -->

	<xsl:template name="post_author">
		<dl class="author">
			<dt><xsl:value-of select="locales/author_title" /></dt>
			<dd itemprop="author"><a href="{user/permalink}"><xsl:value-of select="user/display_name" /></a></dd>
		</dl>
	</xsl:template>


	<!-- Adjacent Posts -->

	<xsl:template match="adjacent">
		<div class="adjacent">
			<xsl:apply-templates />
		</div>
	</xsl:template>

	<xsl:template match="//adjacent/left | //adjacent/right">
		<div>
			<xsl:attribute name="class">
				<xsl:value-of select="local-name()" />
			</xsl:attribute>
			<xsl:call-template name="adjacent_link" />
		</div>
	</xsl:template>

	<xsl:template name="adjacent_link">
		<a href="{post/permalink}"><xsl:value-of select="post/title" disable-output-escaping="yes" /></a>
	</xsl:template>

	
</xsl:stylesheet>