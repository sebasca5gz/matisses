<form id="form1" name="form1" method="post" action="">
    <div class="panel">
        <div class="panel-heading">
            <h3>
               <i class="icon-cogs"></i>
               {$displayName}
            </h3>
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <div class="col-lg-12">
                   <div class="text-center">
                       <h1>{l s='Asociar Categorías'}</h1>
                   </div>
                    {foreach from=$allCategories item=category}
                    <div class="form-group">
                        <div class="col-lg-8">
                            <label for="ctg{$category.id_category}">
                                {l s='Categoría #'}&nbsp;{$category.id_category}:&nbsp;<strong>{$category.name}</strong>
                            </label>
                        </div>
                        <div class="col-lg-4">
                            <label for="txtCtg{$category.id_category}">
                                {l s='Código SAP:'}&nbsp;
                            </label>
                            <input id="txtCtg{$category.id_category}" name="txtCtg{$category.id_category}" type="text">
                        </div>
                    </div>
                    {/foreach}
                </div>
                <div class="text-center">
                    <input  name="updateCodes" class="button" type="submit" value="{l s='Save'}" />
                </div>
            </div>
        </div>
    </div>
</form>