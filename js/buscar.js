function buscarFactura(form_data, objeto, success, onSuccess,callBack) 
{	
	ajax_call2("Ajax/", form_data,
		empezamos,
		exito,
		error,
		terminamos);

	function exito(respuesta) 
	{
		try 
		{
			var datos = $.parseJSON(respuesta);
			if (datos.status == success) 
			{
				$.notify(onSuccess, "success");
				//loading($(objeto),false);									
				callBack(datos.respuesta);
			}
			else 
			{
				$('input[type=submit]').attr('disabled', false);				
				if (typeof datos.mensaje === 'object') 
				{
					$.each(datos.mensaje, function(i, value) 
					{
						$("#" + i).notify(value, 'warning');
						$("#" + i).addClass("error");
					});					
				}
				else 
				{					
					$(objeto).notify(datos.mensaje, 'warning');
				}
			}
		}
		catch (e) 
		{
			$('input[type=submit]').attr('disabled', false);	
			loading($(objeto),false);		
		}
	}
	function error(error) 
	{		
		$('input[type=submit]').attr('disabled', false);
		$(objeto).notify(error, 'warning');
		loading($(objeto),false);
	}
	function terminamos() 
	{
		$('input[type=submit]').attr('disabled', false);
		loading($(objeto),false);
	}
	function empezamos()
	{
		loading($(objeto),true);
	}
	
}	
	
	