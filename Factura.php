<?php

use Clases\Utilidades\MPage;
use Clases\Login\Acceso;
use Clases\Catalogos\Clientes;
use CFDI4\Clases\Factura;
use Clases\Catalogos\Productos;
use CFDI4\Clases\Datos\DFacturaD;
/**
 *
 * @version v2022_1
 *         
 * @author Martin Mata
 *        
 */
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . "/../"));
include_once 'autoload.php';

global $inicio;
$inicio = "../";

$LOGIN = new Acceso();
if(!$LOGIN->estaLogueado())
{
    header('location: http://localhost'.SITIO_HOME.'Login.php');
    exit(0);
}

$CLIENTE = new Clientes();
$FACTURA = new Factura();
$PRODUCTO = new Productos();
if(isset($_GET["id"]))
{
    $factura = $FACTURA->obtener($_GET["id"]);
}
else $factura = 0;

echo MPage::BeginBlock();
?>
	<style>
	  .lineonly
      {
          width:80%;
          text-align: right;
          background: transparent;
          border: none;          
          -webkit-box-shadow: none;
          box-shadow: none;
          borde
      }
	</style>
<?php
echo MPage::EndBlock("head_script");

echo MPage::BeginBlock();
if($factura !== 0 && $factura->FacEstado == FAC_ACTIVO)
    echo "La Factura ya fue timbrada anteriormente con UUID: $factura->FacUUID";
else 
{
?>

	<div class='card'>				
		<form>	
			<div class='card-body'>
				<div class='row mb-4'>
					<div class='col-sm-6 col-md-4'>
					<div class='input-group form-floating mb-3'>
					<input id='CSRF' type='hidden' value='<?php echo $_SESSION["CSRF"];?>' />
					<input id='FacDatos' name="FacDatos" type='hidden' value="0" />
						<select class='form-select' id='FacReceptor' name='FacReceptor'
							 required='required'>
							 <option value="0">Selecciona Cliente</option>
							<?php echo $CLIENTE->listaSelect(($factura === 0)?"0":$factura->FacReceptor); ?>
                        </select> <label for='FacReceptor'> Cliente</label>
                        <span class='input-group-text'> <a class='btn btn-primary box' id='buscarCliente'
								href='<?php echo $inicio;?>Catalogos/Lista/Clientes.php'
							><i class='fa fa-search'></i></a>
							</span>
							<!--   queda pendiente agregar cliente desde aqui 
                        <span class='input-group-text'> <a class='btn btn-primary box' id='agregarCliente'
								href='<?php echo $inicio;?>Dashboard/Catalogos/Clientes.php'
							><i class='fa fa-plus'></i></a>
							</span>
							 -->
                        </div>						
						<div class='input-group form-floating mb-3'>
						<input class='form-control' id='FacFecha' name='FacFecha'
							 required='required' value='<?php echo date("Y-m-d H:i:s");?>' readonly>
							<label for='FacFecha'> Fecha</label>
							<span class='input-group-text'> <button type="button" class='btn btn-primary' onclick="javascript:$('#FacFecha').prop('readonly', false);">
								<i class='fa fa-calendar'></i></button>
							</span>
                        </div>
                        <div class='input-group form-floating mb-3'>
							<input class='form-control' type='text' id='codigo' name='codigo' /> <label for='codigo'>Producto</label>
							<span class='input-group-text'> <a class='btn btn-primary box' id='buscarProducto'
								href='<?php echo $inicio;?>Catalogos/Lista/Productos.php'
							><i class='fa fa-search'></i></a>
							</span>
						</div>
						<div class='form-floating mb-3'>
							<input class='form-control total' id='total' readonly='readonly' name='total'
								type='text' value='0.00'
							/> <label for='total'> Total</label>
						</div>	
						<div class='form-floating mb-3'>	
    						<select class='form-select' id='FacPeriodicidad' name='FacPeriodicidad'
    							 required='required'>
    							 <option value="0" selected>No es factura global</option>
    							<option value="01">Diaria</option>
    							<option value="02">Semanal</option>
    							<option value="03">Quincenal</option>
    							<option value="04">Mensual</option>
    							<option value="05">Bimestral</option>
                            </select> <label for='FacPeriodicidad'> Factura Global Periodica</label>
                        </div>
					</div>
					
					<div class='col-sm-6 col-md-4'>
						<div class='form-floating mb-3'>
							<select class='form-select' id='FacMetodoPago' name='FacMetodoPago'
							 required='required' onchange="javascript:$('#FacFormaPago').trigger('change');">
							 	<option value="PUE" selected="selected">Pago En Una Sola Exibicion</option>
								<option value="PPD" >Pago En Parcialidades o Diferido</option>
                            </select> <label for='FacMetodoPago'> Metodo de pago </label>
							
						</div>
						<div class='form-floating mb-3'>
							<select class='form-select border-danger valida' data-type='condition' data-condition='FacMetodoPago|PUE-99;PPD+99' id='FacFormaPago' name='FacFormaPago'
							 required='required'>
							 	<option value="01" selected="selected">01 Efectivo</option>
                				<option value="02">02 Cheque</option>
                				<option value="03">03 Transferencia</option>
                				<option value="04">04 Tarjeta de credito</option>
                				<option value="05">05 Monedero electronico</option>
                				<option value="06">06 Dinero electronico</option>
                				<option value="07">07 Tarjeta digital</option>
                				<option value="08">08 Vales de despensa</option>
                				<option value="28">28 Tarjeta de debito</option>														
                				<option value="29">29 Tarjeta de servicio</option>	
                				<option value="30">30 Aplicacion de Anticipos</option>		
                				<option value="31">31 Intermediario de Pago</option>													
                				<option value="99">99 Otro</option>
                            </select> <label for='"FacFormaPago"'> Forma de pago </label>
							
						</div>
						<div class='input-group form-floating mb-3'>
							<select class='form-select' id='FacMoneda' name='FacMoneda'
							 required='required'>
							 	<option value="MXN" selected="selected">Peso Mexicano</option>
                				<option value="USD">Dolar Americano</option>
                				<option value="USN">Dolar Estadounidense (Dia Siguiente)</option>
                				<option value="CAD">Dolar Canadiense</option>
                				<option value="EUR">Euro</option>
                            </select> <label for='FacMoneda'> Moneda </label>
                           
                            <input class="input-group form-control"  type="text" id="FacParidad" name="FacParidad" value="1" placeholder="Paridad" />                           
                           
						</div>
						<div class='form-floating mb-3'>
							<input class='form-control border-success valida' id='FacCuenta' name='FacCuenta' type='text'
								data-type='length' data-length='10' value='' /> <label for='FacCuenta'> Cuenta</label>							
						</div>		
						<div class='form-floating mb-3' >	
    						<select class='form-select' id='FacMeses' name='FacMeses' style="display: none;"
    							 required='required'>
    							 <option value="0" selected>No es factura global</option>
    							<option value="01">Enero</option>
    							<option value="02">Febrero</option>
    							<option value="03">Marzo</option>
    							<option value="04">Abril</option>
    							<option value="05">Mayo</option>
    							<option value="06">Junio</option>
    							<option value="07">Julio</option>
    							<option value="08">Agosto</option>
    							<option value="09">Septiembre</option>
    							<option value="10">Octubre</option>
    							<option value="11">Noviembre</option>
    							<option value="12">Diciembre</option>
    							<option value="13">Enero-Febrero</option>
    							<option value="14">Marzo-Abril</option>
    							<option value="15">Mayo-Junio</option>
    							<option value="16">Julio-Agosto</option>
    							<option value="17">Septiembre-Octubre</option>
    							<option value="18">Noviembre-Diciembre</option>    							
                            </select> <label for='FacMeses'> Meses</label>
                        </div>				
					</div>
					<div class='col-sm-6 col-md-4'>						
						<div class='form-floating mb-3'>
							<select class='form-select' id='FacTipoComprobante' name='FacTipoComprobante'>
								<option value="I" selected="selected">Ingreso</option>
                				<option value="E">Egreso</option>
                				<option value="T">Traslado</option>
                				<option value="P">Pago</option>
                                    </select> <label for='FacTipoComprobante'> Tipo de comprobante</label> 
						</div>
						<div class='form-floating mb-3'>
							<select class='form-select' id='FacUsoCFDI' name='FacUsoCFDI'>
								<option value='G01' >G01 - Adquisicion de mercancias</option>
								<option value='G02' >G02 - Devoluciones, descuentos o bonificaciones</option>
								<option value='G03'  selected >G03 - Gastos en general</option>
								<option value='I01' >I01 - Construcciones</option>
								<option value='I02' >I02 - Mobilario y equipo de oficina por inversiones</option>
								<option value='I03' >I03 - Equipo de transporte</option>
								<option value='I04' >I04 - Equipo de computo y accesorios</option>
								<option value='I05' >I05 - Dados, troqueles, moldes, matrices y herramental</option>
								<option value='I06' >I06 - Comunicaciones telefonicas</option>
								<option value='I07' >I07 - Comunicaciones satelitales</option>
								<option value='I08' >I08 - Otra maquinaria y equipo</option>
								<option value='D01' >D01 - Honorarios medicos, dentales y gastos hospitalarios.</option>
								<option value='D02' >D02 - Gastos medicos por incapacidad o discapacidad</option>
								<option value='D03' >D03 - Gastos funerales.</option>
								<option value='D04' >D04 - Donativos.</option>
								<option value='D05' >D05 - Intereses reales efectivamente pagados por creditos hipotecarios (casa habitacion).</option>
								<option value='D06' >D06 - Aportaciones voluntarias al SAR.</option>
								<option value='D07' >D07 - Primas por seguros de gastos medicos.</option>
								<option value='D08' >D08 - Gastos de transportacion escolar obligatoria.</option>
								<option value='D09' >D09 - Depositos en cuentas para el ahorro, primas que tengan como base planes de pensiones.</option>
								<option value='D10' >D10 - Pagos por servicios educativos (colegiaturas)</option>
								<option value='S01' >S01 - Sin Efectos Fiscales</option>
								<option value='CP01' >CP01 - Por definir</option>	
                            </select> <label for='FacUsoCFDI'> Uso de CFDI</label> 
						</div>
						<div class='input-group form-floating mb-3'>
							<input class='form-control' type='text' id='RelUUID' name='RelUUID' /> <label for='codigo'>CFDI Relacionado</label>
							<span class='input-group-text'> <a class='btn btn-info box' id='buscarUUID'
								href='<?php echo $inicio;?>Dashboard/Catalogos/Lista_UUID.php'
							><i class='fa fa-search'></i></a>
							</span>
						</div>
						<div class='form-floating mb-3'>
							<select class='form-select' id='RelTipo' name='RelTipo'>
								<option value="0"> </option>
                				<option value="01">01 Nota de Credito de Documentos Relacionados</option>
                				<option value="02">02 Nota de Debito de Documentos Relacionados</option>
                				<option value="03">03 Devolucion de Mercancia sobre Factura Previa</option>
                				<option value="04">04 Sustitucion de CFDI Previo</option>
                				<option value="05">05 Traslado de Mercancia Facturada Previamente</option>
                				<option value="06">06 Factura Generada por los Traslados Previos</option>
                				<option value="07">07 CFDI por Aplicacion de Anticipo</option>
                                    </select> <label for='RelTipo'> Tipo de relacion</label> 
						</div>
						<div class='form-floating mb-3' >
							<select class='form-select' id='FacYear' name='FacYear' style="display: none;">
							 <option value="0" selected>No es factura global</option>
								<option value="2021">2021</option>
								<option value="2022">2022</option>
                				<option value="2023">2023</option>
                				<option value="2024">2024</option>                				
                                    </select> <label for='FacYear'> A&ntilde;o</label> 
						</div>						
					</div>					
				</div>
				<div class='table-responsive table-responsive-sm'>
					<table class='table table-striped'  id='venta_productos'>
						<thead>
							<tr>
								<th width='5%'>Proser</th>
								<th width='15%'>Unidad</th>
								<th width='25%'>Descripcion</th>
								<th width='10%'>Cantidad</th>
								<th width='10%'>Precio</th>
								<th width='10%'>Importe</th>
							</tr>
						</thead>
						<tbody>
                       <?php if($factura !== 0)
                		{
                		   
                		        foreach ($factura->Detalles as $detalle)
                		        {           
                		            $detalle = new DFacturaD($detalle);
                		            echo
                		            "<tr>".
                		            "<td width='5%' class='center'><span class='noenter ProProSer'>".$detalle->DdeProProSer."</span></td>".
                		            "<td width='5%' class='center'><span class='noenter ProPresentacion'>".$detalle->DdeUnidad."</span></td>".
                		            "<td width='15%'><span class='noenter ProDescripcion' contenteditable='true'>".$detalle->DdeDescripcion."</span></td>".
                                    "<td width='10%'><input class='lineonly noenter ProCantidad' type='number' value='".$detalle->DdeCantidad."'/></td>".
                                    "<td width='10%'><input class='lineonly noenter ProPrecio' type='number' value='".$detalle->DdePrecio."'/></td>".
                                    "<td width='10%'><input class='lineonly noenter ProImporte' type='number' value='".$detalle->DdeImporte."' readonly /></td>".
                                    "<td width='5%' align='right'><button class='cut btn-primary f-minus big' style='margin: 0;padding: 0.5em;'>-</button></td>".
                                    "<td style='display:none;'><span style='display:none;' class='ProIVA'>".$detalle->DdeIva."</span></td>".
                                    "<td style='display:none;'><span style='display:none; ' class='ProID'>".$detalle->DdeProducto."</span></td>".
                                    "<td style='display:none;'><span style='display:none; ' class='ProPredial'>".$detalle->DdePredial."</span></td>".
                                    "<td style='display:none;'><span style='display:none; ' class='ProISR'>".$detalle->DdeISR."</span></td>".
                                    "<td style='display:none;'><span style='display:none; ' class='ProRet'>".$detalle->DdeRet."</span></td>".
                                    "<td style='display:none;'><span style='display:none;' class='ProIEPS'>".$detalle->DdeIeps."</span></td>".
                		            "</tr>";
                		        }
                		        unset($factura->Detalles);
                		        $venta_json = json_encode($factura, JSON_FORCE_OBJECT);                		  
                			
                		}?>
						</tbody>
					</table>
				</div>
				<div class='row'>
					<div class='col-lg-4 col-sm-6'>
						<textarea id="FacNota" style="width: 100%">Notas: </textarea>
					</div>
					<div class='col-lg-4 col-sm-5 ml-auto'>
						<table class='table table-clear'>
							<tbody>
								<tr>
									<td class='left'><strong>Subtotal</strong></td>
									<td class='right'><input class='lineonly twodigits' type='text' id='subtotal' /></td>
								</tr>
								<tr>
									<td class='left'><strong>IVA</strong></td>
									<td class='right'><input class='lineonly twodigits' type='text' id='iva' /></td>
								</tr>
								<tr>
									<td class='left'><strong>Retencion</strong></td>
									<td class='right'><input class='lineonly twodigits' type='text' step='0.00' id='iva_ret1' value='0.00'/></td>
								</tr>
								<tr>
									<td class='left'><strong>ISR</strong></td>
									<td class='right'><input type='text' class='lineonly twodigits' id='iva_isr1' value='0.00' /></td>
								</tr>
                                <tr>
									<td class='left'><strong>IEPS</strong></td>
									<td class='right'><input type='text' class='lineonly twodigits' id='ieps' value='0.00'/></td>
								</tr>
								<tr>
									<td class='left'><strong>Imp Local</strong> <select style='width: 4em;' id='ImpLocal'>
											<option value='0'>0.00</option>
											<option value='1'>RTP-3.00</option>
											<option value='2'>ISH+3.00</option>
										</select></td>
									<td class='right'><input type='text' class='lineonly twodigits' id='ImpLocalVal' value='0.00'/></td>
								</tr>
								<tr>
									<td class='left'><strong>Total</strong></td>
									<td class='right'><input type='text' id='total' class='lineonly total' /></td>
								</tr>
							</tbody>
						</table>
						<button type="button" class='btn btn-primary' id='submitButtonVenta'> Archivar</button> - | -  
						<button type="button" class='btn btn-success' id='timbrarButtonVenta' style="display: none;"> Timbrar</button>
					</div>
				</div>
			</div>
			</form>
		</div>

<?php
}
echo MPage::EndBlock("body");

echo MPage::BeginBlock();
?>
Aqui van los scripts que no estan incluidos en la pagina maestra que son esclusivos de esta pagina javascript
<?php 
echo MPage::EndBlock("foot_script");
MPage::Render("include/", "Forma.php");
?>       