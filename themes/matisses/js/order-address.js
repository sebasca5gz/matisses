/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/



$(document).ready(function(){
	console.log(isGiftAddress,formatedAddressFieldsValuesListGift);
	if (typeof formatedAddressFieldsValuesList !== 'undefined')
		updateAddressesDisplay(true);

	$(document).on('change', 'select[name=id_address_delivery], select[name=id_address_invoice]', function(){		
		updateAddressesDisplay();
		if (typeof opc !=='undefined' && opc)
			updateAddressSelection();
	});
	$(document).on('click', 'input[name=same]', function(){
		updateAddressesDisplay();
		if (typeof opc !=='undefined' && opc)
			updateAddressSelection();
	});
});

//update the display of the addresses
function updateAddressesDisplay(first_view)
{
	// update content of delivery address
	updateAddressDisplay('delivery');
	var txtInvoiceTitle = "";
	try{
		var adrs_titles = getAddressesTitles();
		txtInvoiceTitle = adrs_titles.invoice;
	}
	catch (e)
	{}
	// update content of invoice address
	//if addresses have to be equals...
	if ($('#addressesAreEquals:checked').length === 1 && ($('#multishipping_mode_checkbox:checked').length === 0))
	{
		if ($('#multishipping_mode_checkbox:checked').length === 0) {
			$('#address_invoice_form:visible').hide('fast');
		}
		$('ul#address_invoice li.address_title').html(txtInvoiceTitle);

		if (isGiftAddress > 0) {
			updateAddressDisplay('invoice');
		}else{
			$('ul#address_invoice').html($('ul#address_delivery').html());
		}
	}
	else
	{
		$('#address_invoice_form:hidden').show('fast');
		if ($('#id_address_invoice').val())
			updateAddressDisplay('invoice');
		else
		{
			$('ul#address_invoice').html($('ul#address_delivery').html());
			$('ul#address_invoice li.address_title').html(txtInvoiceTitle);
		}	
	}
	if(!first_view)
	{
		if (orderProcess === 'order')
			updateAddresses();
	}
	return true;
}

function updateAddressDisplay(addressType)
{
	if (typeof formatedAddressFieldsValuesList == 'undefined' || formatedAddressFieldsValuesList.length <= 0)
		return;

	var idAddress = parseInt($('#id_address_' + addressType + '').val());
	buildAddressBlock(idAddress, addressType, $('#address_' + addressType));

	// change update link
	var link = $('ul#address_' + addressType + ' li.address_update a').attr('href');
	var expression = /id_address=\d+/;
	if (link)
	{
		link = link.replace(expression, 'id_address=' + idAddress);
		$('ul#address_' + addressType + ' li.address_update a').attr('href', link);
	}
}

function updateAddresses()
{
	var idAddress_delivery = parseInt($('#id_address_delivery').val());
	var idAddress_invoice = $('#addressesAreEquals:checked').length === 1 ? idAddress_delivery : parseInt($('#id_address_invoice').val());
	
   	if(isNaN(idAddress_delivery) == false && isNaN(idAddress_invoice) == false)	
	{
		$('.addresses .waitimage').show();
		$('.button[name="processAddress"]').prop('disabled', 'disabled');
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: baseUri + '?rand=' + new Date().getTime(),
			async: true,
			cache: false,
			dataType : "json",
			data: {
				processAddress: true,
				step: 2,
				ajax: 'true',
				controller: 'order',
				'multi-shipping': $('#id_address_delivery:hidden').length,
				id_address_delivery: idAddress_delivery,
				id_address_invoice: idAddress_invoice,
				token: static_token
			},
			success: function(jsonData)
			{
				if (jsonData.hasError)
				{
					var errors = '';
					for(var error in jsonData.errors)
						//IE6 bug fix
						if(error !== 'indexOf')
							errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
		            if (!!$.prototype.fancybox)
		                $.fancybox.open([
		                    {
		                        type: 'inline',
		                        autoScale: true,
		                        minHeight: 30,
		                        content: '<p class="fancybox-error">' + errors + '</p>'
		                    }
		                ], {
		                    padding: 0
		                });
		            else
		                alert(errors);
				}
				$('.addresses .waitimage').hide();
				$('.button[name="processAddress"]').prop('disabled', '');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				$('.addresses .waitimage').hide();                        
				$('.button[name="processAddress"]').prop('disabled', '');
				if (textStatus !== 'abort')
				{
					error = "TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
		            if (!!$.prototype.fancybox)
		                $.fancybox.open([
		                    {
		                        type: 'inline',
		                        autoScale: true,
		                        minHeight: 30,
		                        content: '<p class="fancybox-error">' + error + '</p>'
		                    }
		                ], {
		                    padding: 0
		                });
		            else
		                alert(error);
				}
			}
		});
	}
}

function getAddressesTitles()
{
	if (typeof titleInvoice !== 'undefined' && typeof titleDelivery !== 'undefined')
		return {
			'invoice': titleInvoice,
			'delivery': titleDelivery
		};
	else
		return {
			'invoice': '',
			'delivery': ''
		};
}

function buildAddressBlock(id_address, address_type, dest_comp)
{
	if (isNaN(id_address))
		return;
	var adr_titles_vals = getAddressesTitles();
	if (isGiftAddress > 0 && address_type=='invoice') {
		var li_content = formatedAddressFieldsValuesListGift[id_address]['formated_fields_values'];
	}else{
		var li_content = formatedAddressFieldsValuesList[id_address]['formated_fields_values'];
	}
	var ordered_fields_name = ['title'];
	var reg = new RegExp("[ ]+", "g");
	if (isGiftAddress > 0 && address_type=='invoice') {
		ordered_fields_name = ordered_fields_name.concat(formatedAddressFieldsValuesListGift[id_address]['ordered_fields']);
	}else{
		ordered_fields_name = ordered_fields_name.concat(formatedAddressFieldsValuesList[id_address]['ordered_fields']);
	}
	ordered_fields_name = ordered_fields_name.concat(['update']);
	dest_comp.html('');
	li_content['title'] = adr_titles_vals[address_type];
	if (typeof liUpdate !== 'undefined' && typeof fromlist === "undefined")
	{
		var items = liUpdate.split(reg);
		var regUrl = new RegExp('(https?://[^"]*)', 'gi');
		liUpdate = liUpdate.replace(regUrl, addressUrlAdd + parseInt(id_address));
		li_content['update'] = liUpdate;
	}
	appendAddressList(dest_comp, li_content, ordered_fields_name);
}

function appendAddressList(dest_comp, values, fields_name)
{
	
	var label1 = "Nombre:";
	var label2 = "Dirección 1:";
	var label3 = "Dirección 2:";
	var label4 = "Ciudad:";
	var label5 = "Departamento:";
	var label6 = "Teléfono:";
	var label7 = "Celular:";
	
	for (var item in fields_name)
	{
		var name = fields_name[item].replace(",", "");
		var value = getFieldValue(name, values);
		if (value != "")
		{
			var new_li = document.createElement('li');
			var reg = new RegExp("[ ]+", "g");
			var classes = name.split(reg);
			new_li.className = '';
			new_li.label = '';
			 
			for (clas in classes)
			{
				new_li.className += 'address_' + classes[clas].toLowerCase().replace(":", "_") + ' ';
				switch(new_li.className)
				{
					case 'address_firstname address_lastname ': new_li.label = '<b>'+label1+'</b> '; break;
					case 'address_address1 ': new_li.label = '<b>'+label2+'</b> '; break;
					case 'address_address2 ': new_li.label = '<b>'+label3+'</b> '; break;
					case 'address_postcode address_city ': new_li.label = '<b>'+label4+'</b> '; break;
					case 'address_country_name ': new_li.label = '<b>'+label5+'</b> '; break;
					case 'address_phone ': new_li.label = '<b>'+label6+'</b> '; break;
					case 'address_phone_mobile ': new_li.label = '<b>'+label7+'</b> '; break;
				}
				
			}
			
			new_li.className = new_li.className.trim();
			new_li.innerHTML = new_li.label+value;
			dest_comp.append(new_li);
		}
	}
}

function getFieldValue(field_name, values)
{
	var reg = new RegExp("[ ]+", "g");
	var items = field_name.split(reg);
	var vals = new Array();
	for (var field_item in items)
	{
		items[field_item] = items[field_item].replace(",", "");
		vals.push(values[items[field_item]]);
	}
	return vals.join(" ");
}