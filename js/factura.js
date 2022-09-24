function parsePrice(number) 
{
	return number.toFixed(2).replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1,');
}

function parseFloatHTML(element) 
{
	return parseFloat(element.innerHTML.replace(/[^\d\.\-]+/g, "")) || 0;
}

function removecomas(coma)
{
    return coma.replace(/,/g, '');
}
function updateNumber(e) 
{
	var activeElement = document.activeElement, value = parseFloat(activeElement.innerHTML), wasPrice = activeElement.innerHTML == parsePrice(parseFloatHTML(activeElement));
	if (!isNaN(value) && (e.keyCode == 38 || e.keyCode == 40)) 
	{
		e.preventDefault();
		activeElement.innerHTML = wasPrice ? parsePrice(value) : value;
	}	
}

function Venta()
{	
	this.FacID = 0;
    this.FacReceptor = 0;
    this.FacFolio = "0";
    this.FacTotal = 0;
    this.FacIva = 0;
    this.FacDescuento = 0;        
    this.FacTipoComprobante = "I";
    this.FacFormaPago = "PUE";
    this.FacMetodoPago = "01";
    this.FacMotivoDescuento = " ";
    this.FacMoneda = "MXN";
    this.FacParidad = "1";
    this.FacFecha = "";
    this.FacRet = 0;
    this.FacIsr = 0;
    this.FacNota = " ";
    this.FacCuenta = "No Identificado";
    this.FacIEPS = "0|0";
    this.FacSucursal = -1;  

}
function Detalle()
{	
     this.DdeUnidad = "";
    this.DdeDescripcion = "";
    this.DdePrecio = 0;
    this.DdeCantidad = 0;
    this.DdeImporte = 0;
    this.DdeAduanaNumero = 0;
    this.DdeAduanaFecha = "";
    this.DdeDocumento = 0;
    this.DdeProducto = 0;
    this.DdeIva = 0.000;
    this.DdeIeps = 0.000; 
    
}

function obtenerVenta()
{
	factura = new Venta();
	factura.detalles = new Array();
	var total = 0;
	var tempGravado = 0; 
	var tempIeps = 0;
	var tempIepsdatos = " ";
	var tempnoIva = 0;   
	var tempisr = 0;
	var tempret = 0;
	var cells, price, a, i;	
	// update inventory cells
	// ======================
	$("#venta_productos tbody tr").each(function()
		{
		    detalles = new Detalle();
		    $(this).find(".ProImporte").val(($(this).find(".ProPrecio").val()) * ($(this).find(".ProCantidad").val()));
		    price = $(this).find(".ProImporte").val() *1;
		    var iva = $(this).find(".ProIVA").text() *1;
		    var ps = $(this).find(".ProIEPS").text() *1; 
		    var isr = $(this).find(".ProISR").text() *1;
		    var ret = $(this).find(".ProRet").text() *1;
			if(ps > 0.000)
			{
				tempIeps += (price * ps);
				tempIepsdatos += " "+(price * ps)+"|"+(ps * 100)+";";
			}
			if(iva > 0.000)
				tempGravado += price *1;
			else
				tempnoIva += price * 1;		
			if(isr > 0.0000)
			    tempisr += (isr * price);
			    //$("#iva_isr1").text(isr * $(this).find(".ProImporte").text());
			if(ret > 0.000)
			    tempret += (ret * price);
			    //$("#iva_ret1").text(ret * $(this).find(".ProImporte").text());
			detalles.DdeISR = isr ;
			detalles.DdeRet = ret ;
			detalles.DdeDescripcion = $(this).find(".ProDescripcion").text();
			detalles.DdeUnidad = $(this).find(".ProPresentacion").text();
			detalles.DdeProProSer = $(this).find(".ProProSer").text();
			detalles.DdePrecio = $(this).find(".ProPrecio").val();
			detalles.DdeCantidad = $(this).find(".ProCantidad").val();					
			price = $(this).find(".ProImporte").val() *1;
			detalles.DdeImporte = $(this).find(".ProImporte").val() * 1;			
			// agregamos esta celda para incluir tipo de IVA 0 = 16% 1 = 0% 2 = excento
			detalles.DdeIva = $(this).find(".ProIVA").text();
			detalles.DdeProducto = $(this).find(".ProID").text();
			detalles.DdeIeps = $(this).find(".ProIEPS").text();
			detalles.DdeProProSer = $(this).find(".ProProSer").text();
			detalles.DdePredial = $(this).find(".ProPredial").text();
			total += price;
			factura.detalles.push(detalles);
		    
		});

	
	gravado = tempGravado;
	noIva = tempnoIva;
	ieps = tempIeps;
	$("#iva_isr1").text(tempisr);
	$("#iva_ret1").text(tempret);
	// update balance cells
	// ==================== 
	
	
	//Agregamos IVA solo a productos gravados
	factura.FacIva =  tempGravado * 0.16;
	factura.FacSubtotal = total;
	// set balance and meta balance	
	factura.FacTotal = ieps + total + (tempGravado * 0.16) - tempret - tempisr; 
	factura.FacRet = $("#iva_ret1").val();
	factura.FacIsr = $("#iva_isr1").val();	
	factura.FacIEPS = ieps+":"+tempIepsdatos;
	factura.FacSucursal = $("#sucursal").val();
	factura.RelUUID = $("#RelUUID").val();
	factura.RelTipo = $("#RelTipo").val();
	factura.FacDatos = $("#FacDatos").val();
	factura.FacID = $("#FacID").val();
	// get the cfdi data
		
    factura.FacMoneda = $("#FacMoneda").val();
    factura.FacParidad = $("#FacParidad").val();
    factura.FacTipoComprobante = $("#FacTipoComprobante").val();
    factura.FacFormaPago = $("#FacFormaPago").val();
    factura.FacMetodoPago =  $("#FacMetodoPago").val();
    factura.FacCuenta = ($("#FacCuenta").val());
    factura.FacUsoCFDI = $("#FacUsoCFDI").val();
    //factura global
    factura.FacPeriodicidad = $("#FacPeriodicidad").val();
    factura.FacMeses = $("#FacMeses").val();
    factura.FacYear = $("#FacYear").val();
	//////////
	factura.FacReceptor = $("#FacReceptor").val();
	factura.FacFecha = $("#FacFecha").val();
	
	factura.FacNota = $("#FacNota").val();
	if($("#ImpLocalVal").val()*1 > 0)
	{
		factura.FacLocal = $("#ImpLocalVal").val();	
		factura.FacLocalID = $("#ImpLocal").val();
	}
	return factura;
	// update price formatting
	// =======================
}

function calcularVenta()
{	
	var total = 0;
	var tempGravado = 0;  
	var tempIeps = 0;
	var tempIepsdatos = " ";	
	var tempnoIva = 0;
	var totalIva = 0;
	var tempisr = 0;
	var tempret = 0;
	var cells, price, total, a, i;	
	// update inventory cells
	// ======================
	$("#venta_productos tbody tr").each(function()
	{
	    $(this).find(".ProImporte").val(($(this).find(".ProPrecio").val()) * ($(this).find(".ProCantidad").val()));
	    price = $(this).find(".ProImporte").val() *1;
	    var iva = $(this).find(".ProIVA").text() *1;
	    var ps = $(this).find(".ProIEPS").text() *1; 
	    var isr = $(this).find(".ProISR").text() *1;
	   var ret = $(this).find(".ProRet").text() *1;	   
		if(ps > 0.000)
		{
			tempIeps += (price * ps);
			tempIepsdatos += " "+(price * ps)+"|"+(ps * 100)+";";
		}
		if(iva > 0.000)
		{
			tempGravado += price *1;
			//totalIva += parseFloat(toFixed(price * 0.16));
		}
		else
			tempnoIva += price * 1;			
		if(isr > 0.0000)
		    tempisr += (isr * price);
		    //$("#iva_isr1").text(isr * $(this).find(".ProImporte").text());
		if(ret > 0.000)
		    tempret += (ret * price);
		    //$("#iva_ret1").text(ret * $(this).find(".ProImporte").text());
		
		total += price * 1;
	    
	});	
	gravado = tempGravado;
	noIva = tempnoIva;
	ieps = tempIeps;
	
	$("#iva_isr1").val(tempisr);
	$("#iva_ret1").val(tempret);
	 
	// update balance cells
	// ====================

	// get balance cells
	
	// set total
	var retencion = false;
	$("#subtotal").val(total);
	if($("#ImpLocal").val() == "0")
		$("#ImpLocalVal").val("0.00");
	else
	{		
		var temporal = $("#ImpLocal option:selected").text();
		var split1;
		var split2;
		if(temporal.includes('+'))
		{
			split1 = temporal.split("+");
			split2 = split1[1].split(".");
			$("#ImpLocalVal").val(split2[0] * $("#subtotal").val().replace(/\,/g,'') / 100);			
		}
		else if(temporal.includes('-'))
		{
			retencion = true;
			split1 = temporal.split("-");
			split2 = split1[1].split(".");
			$("#ImpLocalVal").val(split2[0] * $("#subtotal").val().replace(/\,/g,'') / 100);			
		}
		
	}	
    				
				
			
	// Se agrega el iva de los productos gravaso 
	//Lo cambiamos para evitar acolumacion de centavos
	$("#iva").val(tempGravado * 0.16);	
	//$("#iva").text(totalIva);
	$("#ieps").val(ieps);
	if(!retencion)
		$(".total").val(ieps + total + $("#iva").val()*1 + $("#ImpLocalVal").val()*1 - $("#iva_ret1").val()*1 - $("#iva_isr1").val()*1);
	else
		$(".total").val(ieps + total + $("#iva").val()*1 - $("#ImpLocalVal").val()*1 - $("#iva_ret1").val()*1 - $("#iva_isr1").val()*1);
	$(".total").val(parsePrice($('.total').val()*1));
	//updateNumber(document.querySelector('#total'));
	// update prefix formatting
	// ========================
	
	
	//var prefix = document.querySelector('#prefix').innerHTML;
	//for (a = document.querySelectorAll('[data-prefix]'), i = 0; a[i]; ++i) a[i].innerHTML = prefix;

	// update price formatting
	// =======================

	$(".twodigits").each(function(i,obj)
	{
		$(obj).val(parseFloat($(obj).val()).toFixed(2));
	});//for (a = document.querySelectorAll('span[data-prefix] + span'), i = 0; a[i]; ++i) if (document.activeElement != a[i]) a[i].innerHTML = parsePrice(parseFloatHTML(a[i]));
}


function onContentLoad() 
{
	document.addEventListener('click', calcularVenta);
	document.addEventListener('keydown', updateNumber);
	document.addEventListener('keyup', calcularVenta);		
}

window.addEventListener && document.addEventListener('DOMContentLoaded', onContentLoad);

