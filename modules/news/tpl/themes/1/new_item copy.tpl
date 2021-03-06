{if false}
<div class="breadcrumb newsBreadcrumb iconNewsList ">
    <a href="{$link->getPageLink('index.php',true)}" >{l s='Home' mod='news'}</a>
    <i class="fa fa-angle-right"></i>
    <span class="navigation_page">
        <a href="{$link->getModuleLink('news', 'list',
                                                            [
                                                                'cat_news' => "{$cat}",
                                                                'page_cat' => "{$page}",
                                                                'rewrite'  => "{$cat_rewrite}"
                                                             ]

                                                            ,false)}" >{l s='News' mod='news'}</a>
    </span>
    <i class="fa fa-angle-right"></i>
    <span class="navigation_page">{$title|escape:html:'UTF-8'|truncate:60:'...'}</span>

    <a class="newsRss" href="{$link->getModuleLink('news', 'rss', ['rss_news'=>1] ,false)}" target="_blank"></a>
</div>
{/if}

<div class="newContent grid_12 omega alpha">
    <div class="newContentInt">

        {if $imgsObj}
            <script type="text/javascript">
                $(function()  {ldelim}


				 $('#newSlideshow a').lightBox( {ldelim}
                        imageLoading:'{$url_to_module}/js/lightbox/images/loading.gif',		// (string) Path and the name of the loading icon
                        imageBtnPrev:'{$url_to_module}/js/lightbox/images/prev.png',			// (string) Path and the name of the prev button image
                        imageBtnNext:'{$url_to_module}/js/lightbox/images/next.png',			// (string) Path and the name of the next button image
                        imageBtnClose:'{$url_to_module}/js/lightbox/images/close.png',		// (string) Path and the name of the close btn
                        imageBlank:'{$url_to_module}/js/lightbox/images/lightbox-blank.gif',
                        txtImage:'{l s='Image' mod='news'}',
                        txtOf:'{l s='of' mod='news'}',
                        fixedNavigation: true
                    });

				{if count($imgsObj)>1}

					$('#newGallery a').lightBox( {ldelim}
                        imageLoading:'{$url_to_module}/js/lightbox/images/loading.gif',		// (string) Path and the name of the loading icon
                        imageBtnPrev:'{$url_to_module}/js/lightbox/images/prev.png',			// (string) Path and the name of the prev button image
                        imageBtnNext:'{$url_to_module}/js/lightbox/images/next.png',			// (string) Path and the name of the next button image
                        imageBtnClose:'{$url_to_module}/js/lightbox/images/close.png',		// (string) Path and the name of the close btn
                        imageBlank:'{$url_to_module}/js/lightbox/images/lightbox-blank.gif',
                        txtImage:'{l s='Image' mod='news'}',
                        txtOf:'{l s='of' mod='news'}',
                        fixedNavigation: true
                    });
					$('#newSlideshow').cycle( {ldelim}
						fx:     'fade',
						speed:  'fast',
						timeout: 5000,
						pagerEvent: 'mouseover',
						pauseOnPagerHover: true ,
						pager:  '#newGallery',
						pagerAnchorBuilder: function(idx, slide)  {ldelim}
							// return sel string for existing anchor
							return '#newGallery a:eq(' + (idx) + ')';
						}
                });

				{/if}
            });

            </script>
        <div class="newImgsContent grid_12 alpha omega" >
            <div class="newSlideshow" id="newSlideshow" >

            	<a href="{$imgsObj[0]->img}" title="{$title|escape:html:'UTF-8'|truncate:220:'...'} -  {$date}">
                        <img src="{$imgsObj[0]->img_slider}"  alt="" width="{$news_slideshow_width}"  height="{$news_slideshow_height}"  />
                    </a>
            </div>

			{if count($imgsObj)>1 && false}
				<div class="newGallery" id="newGallery">
					{foreach from=$imgsObj item='img' name=myLoop}
						<a href="{$img->img}" title="{$title|escape:html:'UTF-8'|truncate:220:'...'} -  {$date}"><img src="{$img->img_thumbnail}"  alt="" width="70" height="30"  /></a>
					{/foreach}
				</div>

			{/if}
        </div>
        {/if}
        <div class="description-news grid_12 alpha omega">
            <div class="newTitle"><h1>{$title}</h1></div>
            <div class="new-author"><p>{if $autor}{$autor} {l s='on' mod='news'}{/if} </p></div>
            <div class="news-date"><span>{$date}</span></div>
            <div class="newText"><p> {$new}</p></div>
        </div>
        <div class="newContentTopRigthSocial grid_12 alpha omega">
            <!-- <div class="share shareFacebook">
            {if $socialButtons[0]=='1'}
                FACEBOOK BTN COUNT
                <iframe src="http://www.facebook.com/plugins/like.php?href={$link->getModuleLink('news', 'new',
                                                            [
                                                                'id_news'  => "{$id_news}",
                                                                'cat_news' => "{$cat}",
                                                                'page_cat'     => 0,
                                                                'rewrite'  => "{$rewrite}",
                                                                'cat_rewrite'  => ""
                                                             ]

                                                            ,false)}?&amp;layout=button_count&amp;show_faces=false&amp;action=like&amp;font=verdana&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:35px;" allowTransparency="true" class="iconShareFacebook"></iframe>
                FACEBOOK BTN COUNT 
            {/if}
            </div>-->
            
            <div class="ax-shareContent">
               <div class="newTopActions">
                    <a href="javascript:window.print()" class="newPrint"><i class="fa fa-print" aria-hidden="true"></i></a>
                    <!-- AddThis Button BEGIN -->
                        <p class="addthis_toolbox addthis_default_style ">
                            <a class="addthis_button_email" ></a>
                        </p>
                        <script type="text/javascript" src="https://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4fd60a055ec70816"></script>
                    <!-- AddThis Button END -->
                </div>
                <div id="share"></div>
            </div>
            {if $socialButtonHtml}
            <div>
                {$socialButtonHtml}
            </div>
            {/if}

        </div>

        <div class="ax-pagination_article">
        {if !empty($prev_id_news)}
            <a class="prev btn btn-default btn-red" href="{$link->getModuleLink('news', 'new',
                                        [
                                            'id_news'  => "{$prev_id_news}",
                                            'cat_news' => "{if $cat}{$cat}{/if}",
                                            'page_cat'     => "{$page}",
                                            'rewrite'  => "{$prev_rewrite}",
                                            'cat_rewrite'  => "{$prev_cat_rewrite}"
                                         ]
                                         ,false)}"><i class="fa fa-angle-double-left"></i>&nbsp;{l s='Anterior' mod='news'}</a>
        {/if}
        
        {if !empty($next_id_news)}
            <a class="next btn btn-default btn-red" href="{$link->getModuleLink('news', 'new',
                                        [
                                            'id_news'  => "{$next_id_news}",
                                            'cat_news' => "{if $cat}{$cat}{/if}",
                                            'page_cat'     => "{$page}",
                                            'rewrite'  => "{$next_rewrite}",
                                            'cat_rewrite'  => "{$next_cat_rewrite}"
                                         ]
                                         ,false)}">{l s='Siguiente' mod='news'}&nbsp;<i class="fa fa-angle-double-right"></i></a>
        {/if}
        </div>

        {if $tagsObj}
            <div class="newItemTags" >
                <span>{l s='TAGS ' mod='news'}</span>
                {foreach from=$tagsObj item='tag' name=myLoop}
                <a class="newItemTag" href="{$link->getModuleLink('news', 'news', ['tag_news'=>"{$tag->id}"] ,false)}" title="{$tag->tag|escape:html:'UTF-8'|truncate:100:'...'}">{$tag->tag|escape:html:'UTF-8'|truncate:100:'...'}</a>
                {/foreach}
            </div>
        {/if}
        <div class="newsSep"></div>


        {if $newsProductsObj}
         <div class="newsProductsContent">
           <div class="newsProductsTitle">{l s='Related Products' mod='news'}</div>
           {foreach from=$newsProductsObj item=newsProducts name=myLoop}

                <div class="ajax_block_product newsProductItem">
                    <div class="newsProductImg">
                        <a class="product_img_link" href="{$link->getProductLink($newsProducts->id_product, $newsProducts->link_rewrite, $newsProducts->category_rewrite)}"><img src="{$newsProducts->cover}" height="80" width="80" /></a>
                        <br>
                        {if $newsProducts->show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
                            <span class="price">
                                 {if !$newsProducts->with_tax}{convertPrice price=$newsProducts->price}{else}{convertPrice price=$newsProducts->price_tax}{/if}
                            </span>
                        {/if}
                    </div>
                    <div  class="newsProductInf">
                        <span class="newsProductTitle">{$newsProducts->name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</span>
                        <span class="newsProductDescription">{$newsProducts->description_short|strip_tags|truncate:100:'...'}</span>

                        <a class="button" href="{$link->getProductLink($newsProducts->id_product, $newsProducts->link_rewrite, $newsProducts->category_rewrite)}" title="{l s='View' mod='news'}">{l s='View' mod='news'}</a>

                        {if $newsProducts->show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
                            {if ($newsProducts->id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $newsProducts->available_for_order AND !isset($restricted_country_mode) AND $newsProducts->minimal_quantity == 1 AND $newsProducts->customizable != 2 AND !$PS_CATALOG_MODE}
                                {if ($newsProducts->quantity > 0 OR $newsProducts->allow_oosp)}
                                <a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$newsProducts->id_product}" href="{$link->getPageLink('cart.php',true,NULL,"qty=1&amp;id_product={$newsProducts->id_product}&amp;token={$static_token}&amp;add")}" title="{l s='Add to cart' mod='news'}">{l s='Add to cart' mod='news'}</a>
                                {else}
                                <span class="exclusive">{l s='Add to cart' mod='news'}</span>
                                {/if}
                           {/if}
                       {/if}
                </div>
             </div>

         {/foreach}
        </div>
        <div class="newsSep"></div>
        {/if}


        {if $relNewsObj}
            <div class="relNewsContent" >
                <div class="relNewsTitle">{l s='Related' mod='news'}</div>
                {foreach from=$relNewsObj item='rel' name=myLoop}
                    <a class="relNews" href="{$link->getModuleLink('news', 'new',
                                                            [
                                                                'id_news'  => "{$rel->id}",
                                                                'cat_news' => 0,
                                                                'page_cat'     => 0,
                                                                'rewrite'  => "{$rel->rewrite}",
                                                                'cat_rewrite'  => ""
                                                             ]

                                                            ,false)}" title="{$rel->title|escape:html:'UTF-8'|truncate:70:'...'}"><span>{$rel->date}</span>  {$rel->title|escape:html:'UTF-8'}</a>
                {/foreach}
            </div>
        {/if}


        {if $fbComments}
            <div class="newComments">
                <div id="fb-root"></div>
                <script  type="text/javascript" >(function(d, s, id)  {ldelim}
                  var js, fjs = d.getElementsByTagName(s)[0];
                  if (d.getElementById(id)) return;
                  js = d.createElement(s); js.id = id;
                  js.src = "http://connect.facebook.net/{$fbCommentsLang}/all.js#xfbml=1";
                  fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
                <div class="fb-comments" data-href="{$link->getModuleLink('news', 'new',
                                                            [
                                                                'id_news'  => "{$id_news}",
                                                                'cat_news' => 0,
                                                                'page_cat'     => 0,
                                                                'rewrite'  => "{$rewrite}",
                                                                'cat_rewrite'  => ""
                                                             ]

                                                            ,false)}" data-num-posts="20" data-width="{$newsWidth-10}"></div>
            </div>
        {/if}

        {if ($prev_id_news||$next_id_news) && false }
            <div class="newPrevNext">
                {if $prev_id_news}
                    <a class="button" style="float: left"
                       href="{$link->getModuleLink('news', 'new',
                                                            [
                                                                'id_news'  => "{$prev_id_news}",
                                                                'cat_news' => "{$cat}",
                                                                'page_cat'     => 0,
                                                                'rewrite'  => "",
                                                                'cat_rewrite'  => ""
                                                             ]

                                                            ,false)}"
                       >{l s='< Previous' mod='news'}</a>
                {/if}
                {if $next_id_news}
                    <a class="button" style="float: right"
                       href="{$link->getModuleLink('news', 'new',
                                                            [
                                                                'id_news'  => "{$next_id_news}",
                                                                'cat_news' => "{$cat}",
                                                                'page_cat'     => 0,
                                                                'rewrite'  => "",
                                                                'cat_rewrite'  => ""
                                                             ]

                                                            ,false)}"
                       >{l s='Next >' mod='news'}</a>
                {/if}

            </div>
        {/if}

    </div>
{if $cookie->id_customer}
<div class="row">
	<div class="error" id="error-comment" style="display:none">
    	{l s='Ingresa un comentario' mod='news'}
    </div>
    <form id="form1" name="form1" method="post" action="">

        <div class="form-group">
			<label for="asunto">{l s='Comentario:' mod='news'}</label>
			<textarea class="form-control" max="200" maxlength="200" id="comment"></textarea> 
		</div>
        <div class="grid_4">
        	<button type="button" class="btn btn-default button btn-red right" id="addComment">{l s='Guardar'}</button>
        </div>
    </div>
	</form>

</div>

{if $comments}
<div class="comments">
	{$comments}
</div>
{/if}

{/if}
