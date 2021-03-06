<!-- Module matisses -->
<div class="product_schemes grid_6 alpha omega">
    <ul id="product_schemes">
    	<li id="product-360" class="{if $schemas.three_sixty}active{/if}">
        	<a class="product-360" href="javascript:void(0)" data-option="360" {if $schemas.three_sixty} data-url="{$schemas.three_sixty}" {/if}>
            <img src="{$module_dir}img/360.png" class="img-responsive" />
            <h3>{l s='360' mod='matisses'}</h3>
            </a>
        </li>
        <li id="product-scheme" class="{if $schemas.sketch}active{/if}" >
        	<a class="product-scheme" href="javascript:void(0)" data-option="scheme" {if $schemas.sketch} data-url="{$schemas.sketch}" {/if}>
        	<img src="{$module_dir}img/scheme.png" class="img-responsive" />
            <h3>{l s='Scheme' mod='matisses'}</h3>
            </a>
        </li>
        <li id="product-wow" class="{if $schemas.video}active{/if}">
        	<a href="javascript:void(0)"  class="product-wow" data-option="wow" {if $schemas.video} data-url="{$schemas.video}" {/if}>
        	<img src="{$module_dir}img/wow.png" class="img-responsive" />
            <h3>{l s='Wow' mod='matisses'}</h3>
            </a>
        </li>
    </ul>
</div>


