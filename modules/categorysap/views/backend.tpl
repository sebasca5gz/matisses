<form id="form1" name="form1" method="post" class="form-inline" action="">
   {if isset($saveMsg)}
        <br>
        <div class="alert alert-success"><p>{$saveMsg}</p></div>
        <br>
    {/if}
    {if isset($errorMsg)}
        <br>
        <div class="alert alert-error"><p>{$errorMsg}</p></div>
        <br>
    {/if}
    <div class="panel">
        <div class="panel-heading">
            <h3>
                &nbsp;
                <i class="icon-cogs"></i>
                {$displayName}
            </h3>
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <h2 style="text-transform: uppercase;" class="text-center">{l s='Asociar Categorias'}</h2>
                    <div class="col-lg-12">
                    <table class="table table-bordered">
                       <thead>
                           <tr>
                               <th>N° CATEGORIA</th>
                               <th>ASOCIACION SAP</th>
                           </tr>
                       </thead>
                        <tbody>
                        {foreach from=$allCategories item=category}
                            <tr>
                                <td>
                                    <strong>{l s='Categoría N°'}<span>{$category.id_category}</span></strong>
                                </td>
                                <td>
                                    <div class="col-lg-12">
                                        <div class="col-lg-6 text-right">
                                            <label for="txtCtg{$category.id_category}">
                                                {l s='Subgrupo SAP'}&nbsp;<strong style="text-transform: uppercase; color: #d7162f;">{$category.nameCat}</strong>&nbsp;
                                            </label>
                                        </div>
                                        <div class="col-lg-6 text-left">
                                               <input id="txtCtg{$category.id_category}" name="txtCtg[{$category.id_category}]" type="text" value="{$category.subgrupo}">
                                        </div>                                        
                                    </div>  
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            <div class="col-lg-12 text-center">
                <br>
                <input  name="updateCodes" class="btn btn-success" type="submit" value="{l s='Save'}" />
            </div>
        </div>
        </div>
    </div>
</form>