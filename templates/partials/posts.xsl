<?xml version="1.0" encoding="UTF-8"?>
<!--
	@author Paul van der Meijs <code@paulvandermeijs.nl>
	@copyright Copyright (c) 2012-2013, Paul van der Meijs
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
					<xsl:variable name="is_404" select="boolean(//query/@is_404)" />
					<xsl:variable name="search" select="boolean(//query/@search)" />
					<!-- <xsl:variable name="tax" select="boolean(//query/@tax)" /> -->
					<xsl:variable name="front_page" select="boolean(//query/@front_page)" />
					<xsl:variable name="home" select="boolean(//query/@home)" />
					<xsl:variable name="singular" select="boolean(//query/@singular)" />
					<xsl:variable name="single" select="boolean(//query/@single)" />
					<!-- <xsl:variable name="page" select="boolean(//query/@page)" /> -->
					<!-- <xsl:variable name="attachment" select="boolean(//query/@attachment)" /> -->
					<!-- <xsl:variable name="category" select="boolean(//query/@category)" /> -->
					<!-- <xsl:variable name="tag" select="boolean(//query/@tag)" /> -->
					<!-- <xsl:variable name="author" select="boolean(//query/@author)" /> -->
					<!-- <xsl:variable name="date" select="boolean(//query/@date)" /> -->
					<!-- <xsl:variable name="archive" select="boolean(//query/@archive)" /> -->
					<!-- <xsl:variable name="comments_popup" select="boolean(//query/@comments_popup)" /> -->
					<!-- <xsl:variable name="paged" select="boolean(//query/@paged)" /> -->

					<xsl:choose>
						<xsl:when test="$singular">
							<xsl:apply-templates select="post" mode="singular" />
						</xsl:when>
						<xsl:when test="$search">
							<xsl:apply-templates select="post" mode="search" />
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
		<xsl:param name="singular" select="false()" />
		<xsl:param name="search" select="false()" />

		<xsl:variable name="postFormat" select="taxonomies/taxonomy[query_var='post_format']/terms/term[1]/slug" />
		
		<article id="{name}" data-id="{@id}" itemscope="itemscope" itemtype="http://schema.org/Article">
			<xsl:call-template name="post_class">
				<xsl:with-param name="singular"><xsl:value-of select="$singular" /></xsl:with-param>
			</xsl:call-template>

			<!-- Thumbnail -->

			<xsl:apply-templates select="thumbnail">
				<xsl:with-param name="link">
					<xsl:if test="not(boolean($singular))">
						<xsl:value-of select="permalink" />
					</xsl:if>
				</xsl:with-param>
			</xsl:apply-templates>

			<!-- Header -->

			<xsl:call-template name="post_header">
			</xsl:call-template>

			<!-- Content -->
			
			<xsl:call-template name="post_content">
				<xsl:with-param name="singular" select="$singular" />
			</xsl:call-template>

			<!-- Footer -->

			<xsl:apply-templates select="custom_fields" />
			<xsl:apply-templates select="adjacent" />
			<xsl:call-template name="comments">
				<xsl:with-param name="status" select="comment_status" />
			</xsl:call-template>
		</article>
	</xsl:template>
	
	
	<!-- Single post -->

	<xsl:template match="post" mode="singular">
		<xsl:apply-templates select=".">
			<xsl:with-param name="singular" select="true()" />
		</xsl:apply-templates>
	</xsl:template>
	
	
	<!-- Search result -->

	<xsl:template match="post" mode="search">
		<article id="{name}" data-id="{@id}" itemscope="itemscope" itemtype="http://schema.org/Article">
			<xsl:call-template name="post_class" />
			
			<xsl:apply-templates select="thumbnail">
				<xsl:with-param name="link" select="permalink" />
			</xsl:apply-templates>
			
			<xsl:call-template name="post_header" />
		</article>
	</xsl:template>


	<!-- Post class -->

	<xsl:template name="post_class">
		<xsl:param name="singular" />

		<xsl:attribute name="class">
			<xsl:text>post type-</xsl:text><xsl:value-of select="type" />
			<xsl:for-each select=".//term">
				<xsl:text> </xsl:text><xsl:value-of select="taxonomy" />-<xsl:value-of select="slug" />
			</xsl:for-each>
			<xsl:if test="$singular">
				<xsl:text> singular</xsl:text>
			</xsl:if>
		</xsl:attribute>
	</xsl:template>


	<!-- Post header -->

	<xsl:template name="post_header">
		<xsl:param name="singular" />

		<header>
			<h1>
				<xsl:choose>
					<xsl:when test="$singular">
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


	<!-- Post content -->

	<xsl:template name="post_content">
		<xsl:param name="singular" />

		<div class="content">
			<xsl:choose>
				<xsl:when test="$singular">
					<xsl:value-of select="content" disable-output-escaping="yes" />
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="excerpt" disable-output-escaping="yes" />
				</xsl:otherwise>
			</xsl:choose>
		</div>
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