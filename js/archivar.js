function archivarFactura(form_data, objeto, success, onSuccess,callBack, ismodal, modal, reload) 
{
	if (typeof (ismodal) === 'undefined') ismodal = false;
	if (typeof (modal) === 'undefined') modal = null;
	if (typeof (reload) === 'undefined') reload = false;
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
				loading($(objeto),false);	
				if (ismodal)
				{
					$(modal).modal("toggle");
					$.colorbox.close();
				}
				if(reload)
					location.reload();
				else callBack(datos.respuesta);
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