$('document').ready(function(){
   
    $("#ax-buscar").validate({
        lang: 'es',
        rules:{
            name:'required',
            lastname:'required',
            code:'required',
        },
        message:{
            required:"El campo es requerido"
        }
    });    
    
   $("#name").change(function(){
	   if($("#name").val() !== "" || $("#lastname").val() !== ""){
		   disableCodeField();
	   }
	   else{
		   enableCodeField();
	   }
   });
   
   $("#lastname").change(function(){
	   if($("#name").val() !== "" || $("#lastname").val() !== ""){
		   disableCodeField();
	   }
	   else{
		   enableCodeField();
	   }
   });
   
   $("#code").change(function(){
	   if($("#code").val() != ""){
		   disableNameLastnameFields();
	   }
	   else{
		   enableNameLastnameFields();
	   }
   });
});

function disableCodeField(){
   $("#code").attr("disabled",true);
}

function enableCodeField(){
   $("#code").attr("disabled",false);
}

function enableNameLastnameFields(){
   $("#name").attr("disabled",false);
   $("#lastname").attr("disabled",false);
}

function disableNameLastnameFields(){
   $("#name").attr("disabled",true);
   $("#lastname").attr("disabled",true);
}