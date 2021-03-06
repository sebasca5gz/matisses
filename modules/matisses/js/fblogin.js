// JavaScript Document
window.fbAsyncInit = function() {
	FB.init({
	  appId      : '798901730255416',
	  xfbml      : true,
	  version    : 'v2.5'
	});
};

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

(function(d, s, id){
 var js, fjs = d.getElementsByTagName(s)[0];
 if (d.getElementById(id)) {return;}
 js = d.createElement(s); js.id = id;
 js.src = "//connect.facebook.net/en_US/sdk.js";
 fjs.parentNode.insertBefore(js, fjs); 
}(document, 'script', 'facebook-jssdk'));


fb_login = function(){
	FB.login(function(response) {
		if (response.authResponse) {
			access_token = response.authResponse.accessToken; 
			
			user_id = response.authResponse.userID; 
			FB.api(user_id,{ locale: 'en_US', fields: 'name,email,about,gender,last_name,first_name,birthday' }, function(response) {
                var vars = getUrlVars();
				$.post(ajaxurl, {option: 'fblogin', 'request': response,back : vars.back}, function(response) {
					response = JSON.parse(response);
					if(response.action=='create')
					{
						submitFacebookFunction(response.data);
					}
					
					if(response.action=='reload')
					{
						window.location.href = response.url;
					}
					
				})
			});

		} else {
			console.log('User cancelled login or did not fully authorize.');
		}
	}, {
		scope: 'publish_stream,email'
	});
}

$(document).ready(function(e) {
    if($('.authentication').size()>0 && localStorage.facebooklogin)
	{
		$('#passwd').val(localStorage.passwd).parent().addClass('hidden');
		$('#passwd2').val(localStorage.passwd).parent().addClass('hidden');		
	}
});


function submitFacebookFunction(data)
{
	localStorage.removeItem('facebooklogin');
	localStorage.removeItem('passwd');
	
	
	$('#create_account_error').html('').hide();
	$.ajax({
		type: 'POST',
		url: baseUri,
		async: true,
		cache: false,
		dataType : "json",
		data: 
		{
			controller: 'authentication',
			SubmitCreate: 1,
			ajax: true,
			email_create: data.email,
			back: $('input[name=back]').val(),
			token: token
		},
		success: function(jsonData)
		{
			if (jsonData.hasError) 
			{
				var errors = '';
				for(error in jsonData.errors)
					//IE6 bug fix
					if(error != 'indexOf')
						errors += '<li>' + jsonData.errors[error] + '</li>';
				$('#create_account_error').html('<ol>' + errors + '</ol>').show();
			}
			else
			{
				// adding a div to display a transition
				$('#center_column').html('<div id="noSlide">' + $('#center_column').html() + '</div>');
				$('#noSlide').fadeOut('slow', function()
				{
					$('#noSlide').html(jsonData.page);
					$(this).fadeIn('slow', function()
					{
						if (typeof bindUniform !=='undefined')
							bindUniform();
						if (typeof bindStateInputAndUpdate !=='undefined')
							bindSteInputAndUpdate();
						document.location = '#account-creation';
					});
					
					localStorage.passwd = data.id;
					localStorage.facebooklogin = true;
					$('#passwd').val(localStorage.passwd).parent().addClass('hidden');
					$('#passwd2').val(localStorage.passwd).parent().addClass('hidden');					
					$('#customer_lastname').val(data.last_name);
					$('#customer_firstname').val(data.first_name);
					$('#customer_acount_type').val('facebook');
					$('#facbookid').val(localStorage.passwd);
				});
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			error = "TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
			if (!!$.prototype.fancybox)
			{
			    $.fancybox.open([
		        {
		            type: 'inline',
		            autoScale: true,
		            minHeight: 30,
		            content: "<p class='fancybox-error'>" + error + '</p>'
		        }],
				{
			        padding: 0
			    });
			}
			else
			    alert(error);
		}
	});
}
