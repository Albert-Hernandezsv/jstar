<?php

require_once "../../../controladores/facturas.controlador.php";
require_once "../../../modelos/facturas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once '../../phpqrcode/qrlib.php';


if(isset($_GET["idFactura"]) && isset($_GET["idFactura"])){

    $item = "id";
    $orden = "id";
    $valor = $_GET["idFactura"];
    $optimizacion = "no";

    // Obtiene los datos de la factura
    $factura = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $item = "id";
    $orden = "id";
    $valor = $factura["id_cliente"];

    // Obtiene los datos de la factura
    $cliente = ControladorClientes::ctrMostrarClientes($item, $valor, $orden);

    $item = "id";
    $orden = "id";
    $valor = "1";

    // Obtiene los datos de la factura
    $empresa = ControladorClientes::ctrMostrarEmpresas($item, $valor, $orden);


  }

  function numeroALetras($numero) {
    $unidad = [
        "cero", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho", "nueve",
        "diez", "once", "doce", "trece", "catorce", "quince", "dieciséis", "diecisiete", "dieciocho", "diecinueve"
    ];
    $decena = [
        "", "diez", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta", "setenta", "ochenta", "noventa"
    ];
    $centena = [
        "", "cien", "doscientos", "trescientos", "cuatrocientos", "quinientos", "seiscientos", "setecientos", "ochocientos", "novecientos"
    ];

    if ($numero == 0) {
        return "cero";
    }

    if ($numero < 20) {
        return $unidad[$numero];
    } elseif ($numero < 100) {
        return $decena[intval($numero / 10)] . ($numero % 10 == 0 ? "" : " y " . $unidad[$numero % 10]);
    } elseif ($numero < 1000) {
        return ($numero == 100 ? "cien" : $centena[intval($numero / 100)] . ($numero % 100 == 0 ? "" : " " . numeroALetras($numero % 100)));
    } elseif ($numero < 1000000) {
        return numeroALetras(intval($numero / 1000)) . " mil" . ($numero % 1000 == 0 ? "" : " " . numeroALetras($numero % 1000));
    } elseif ($numero < 1000000000) {
        return numeroALetras(intval($numero / 1000000)) . " millón" . ($numero % 1000000 == 0 ? "" : " " . numeroALetras($numero % 1000000));
    } else {
        return "Número demasiado grande";
    }
    }

    function numeroAmoneda($numero) {
        $partes = explode(".", number_format($numero, 4, ".", ""));
        $parteEntera = intval($partes[0]);
        $parteDecimal = intval($partes[1]);

        $texto = numeroALetras($parteEntera) . " dólares";
        if ($parteDecimal > 0) {
            $texto .= " con " . numeroALetras($parteDecimal) . " centavos";
        }

        return ucfirst($texto);
    }

  // URL que deseas codificar en el QR
  $url = "https://admin.factura.gob.sv/consultaPublica?ambiente=01&codGen=" . $factura["codigoGeneracion"];
  $url .= "&fechaEmi=" . $factura["fecEmi"];  

// Nombre del archivo donde se guardará el QR
$archivoQR = 'codigo_qr.png';

// Genera el código QR y guárdalo como imagen
QRcode::png($url, $archivoQR, QR_ECLEVEL_L, 10);

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    //Page header
    public function Header() {
        
        $item = "id";
        $orden = "id";
        $valor = $_GET["idFactura"];
        $optimizacion = "no";

        // Obtiene los datos de la factura
        $factura = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

        $modoTexto = "";

        if($factura["modo"] != "Contingencia"){
            $modoTexto = "Transmisión normal";
        } else {
            $modoTexto = "Transmisión en contingencia";
        }

        switch ($factura["tipoDte"]) {
            case "01":
                $tipoFacturaTexto = "Factura";
                break;
            case "03":
                $tipoFacturaTexto = "Comprobante de crédito fiscal";
                break;
            case "04":
                $tipoFacturaTexto = "Nota de remisión";
                break;
            case "05":
                $tipoFacturaTexto = "Nota de crédito";
                break;
            case "06":
                $tipoFacturaTexto = "Nota de débito";
                break;
            case "07":
                $tipoFacturaTexto = "Comprobante de retención";
                break;
            case "08":
                $tipoFacturaTexto = "Comprobante de liquidación";
                break;
            case "09":
                $tipoFacturaTexto = "Documento contable de liquidación";
                break;
            case "11":
                $tipoFacturaTexto = "Factura de exportación";
                break;
            case "14":
                $tipoFacturaTexto = "Factura de sujeto excluido";
                break;
            case "15":
                $tipoFacturaTexto = "Comprobante de donación";
                break;

            default:
                echo "Factura no válida";
                break;
        }

        $item = "id";
        $orden = "id";
        $valor = "1";

        // Obtiene los datos de la factura
        $empresa = ControladorClientes::ctrMostrarEmpresas($item, $valor, $orden);

        $this->Ln(5); // Agrega un espacio vertical de 10 unidades (puedes ajustar el valor)
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(250, 10, $empresa["nombre"], 0, 1, 'C', 0, ' ', 1, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(250, 0, "DOCUMENTO TRIBUTARIO ELECTRÓNICO", 0, 1, 'C', 0, ' ', 1, false, 'M', 'M');

        $this->Ln(15); // Agrega un espacio vertical de 15 unidades
        $this->SetFont('helvetica', 'B', 8);

        // Ruta de la imagen del código QR
        $archivoQR = 'codigo_qr.png'; // Ajusta según la ruta de tu archivo QR

        // Inserta el código QR en el PDF
        $this->Image($archivoQR, 50, 5, 20, 20, 'PNG', '', 'C', false, 300, '', false, false, 0, false, false, false);


        $this->Ln(5); // Agrega un espacio vertical de 10 unidades (puedes ajustar el valor)
        $this->SetFont('helvetica', '', 8);
        $this->Cell(220, 20, $modoTexto, 0, true, 'C', 0, ' ', 1, false, 'B', 'M');
        $this->Cell(225, 30, $tipoFacturaTexto.' - '.$factura["estado"], 0, true, 'C', 0, ' ', 1, false, 'B', 'M');

        
        // Logo
        $image_file = K_PATH_IMAGES.'tcpdf_logo.jpg';
        $this->Image($image_file, 10, 5, 35, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetAutoPageBreak(true, 10);


// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Rentaly El Salvador');
$pdf->setTitle('Factura '.$factura["codigoGeneracion"].'');
$pdf->setSubject('Reservación Rentaly El Salvador');
$pdf->setKeywords('	TCPDF, PDF, example, test, guide');


// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);



// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->setFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

// Obtener la fecha de ahora
$dia = date('d');
$mes = date('m');
$ano = date('Y');

function fechaEnEspanol($fecha) {
    $meses = array(
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    );

    $timestamp = strtotime($fecha);
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp)];
    $anio = date('Y', $timestamp);

    return "$dia de $mes del $anio";
}
if($factura == null){
    $html = 'Factura eliminada';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    //Close and output PDF document
    $pdf->Output($factura["codigoGeneracion"].'.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

} else {

    $departamentos = [
        "00" => "Extranjero",
        "01" => "Ahuachapan",
        "02" => "Santa Ana",
        "03" => "Sonsonate",
        "04" => "Chalatenango",
        "05" => "La Libertad",
        "06" => "San Salvador",
        "07" => "Cuscatlán",
        "08" => "La Paz",
        "09" => "Cabañas",
        "10" => "San Vicente",
        "11" => "Usulután",
        "12" => "San Miguel",
        "13" => "Morazán",
        "14" => "La Unión"
    ];
    
    $municipios = [
        "01" => [ // Ahuachapan
            "13" => "Ahuachapan norte",
            "14" => "Ahuachapan centro",
            "15" => "Ahuachapan sur"
        ],
        "02" => [ // Santa Ana
            "14" => "Santa Ana norte",
            "15" => "Santa Ana centro",
            "16" => "Santa Ana este",
            "17" => "Santa Ana oeste"
        ],
        "03" => [ // Sonsonate
            "17" => "Sonsonate norte",
            "18" => "Sonsonate centro",
            "19" => "Sonsonate este",
            "20" => "Sonsonate oeste"
        ],
        "04" => [ // Chalatenango
            "34" => "Chalatenango norte",
            "35" => "Chalatenango centro",
            "36" => "Chalatenango sur"
        ],
        "05" => [ // La Libertad
            "23" => "La Libertad norte",
            "24" => "La Libertad centro",
            "25" => "La Libertad oeste",
            "26" => "La Libertad este",
            "27" => "La Libertad costa",
            "28" => "La Libertad sur"
        ],
        "06" => [ // San Salvador
            "20" => "San Salvador norte",
            "21" => "San Salvador oeste",
            "22" => "San Salvador este",
            "23" => "San Salvador centro",
            "24" => "San Salvador sur"
        ],
        "07" => [ // Cuscatlán
            "17" => "Cuscatlán norte",
            "18" => "Cuscatlán sur"
        ],
        "08" => [ // La Paz
            "23" => "La Paz oeste",
            "24" => "La Paz centro",
            "25" => "La Paz este"
        ],
        "09" => [ // Cabañas
            "10" => "Cabañas oeste",
            "11" => "Cabañas este"
        ],
        "10" => [ // San Vicente
            "14" => "San Vicente norte",
            "15" => "San Vicente sur"
        ],
        "11" => [ // Usulután
            "24" => "Usulután norte",
            "25" => "Usulután este",
            "26" => "Usulután oeste"
        ],
        "12" => [ // San Miguel
            "21" => "San Miguel norte",
            "22" => "San Miguel centro",
            "23" => "San Miguel oeste"
        ],
        "13" => [ // Morazán
            "27" => "Morazán norte",
            "28" => "Morazán sur"
        ],
        "14" => [ // La Unión
            "19" => "La Unión norte",
            "20" => "La Unión sur"
        ]
    ];

    $item = null;
    $valor = null;

    $usuarios = ControladorUsuarios::ctrMostrarUsuarios($item, $valor);

    $nombreVendedor = "";
    $nombreFacturador = "";

    foreach ($usuarios as $key => $value){
        if($value["id"] == $factura["id_vendedor"]){
            $nombreVendedor = $value["nombre"];
        }

        if($value["id"] == $factura["id_usuario"]){
            $nombreFacturador = $value["nombre"];
        }
    }
    $fechaObjeto = DateTime::createFromFormat("Y-m-d", $factura["fecEmi"]);
    $fechaFormateada = $fechaObjeto->format("d-m-Y");
// create some HTML content
$html = '<div style="font-family: Arial, sans-serif; font-size: 8px;">
<hr>
<table border="0" cellspacing="0" cellpadding="2">
    <tr>
        
        <td style="text-align:left;" colspan="9">
            <p style="font-size: 11px"><b>Código de generación:</b>'.$factura["codigoGeneracion"].'</p>
            <p style="font-size: 11px"><b>Número de control:</b>'.$factura["numeroControl"].'</p><br>
            <b>Sello de recepción:</b> '.$factura["sello"].'
        </td>
        <td style="text-align:left; border-left: 1px solid black; height: 60px;" colspan="7">
            <br><br><br><br>
            <b>Sistema de facturación:</b> Fox Control<br>
            <b>Tipo de transmisión:</b> Normal<br>
            <b>Fecha y hora:</b> '.$fechaFormateada.' '.$factura["horEmi"].'
        </td>
    </tr>
</table>

<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #dddcdc" colspan="7">
            <b>EMISOR</b>
        </td>
        <td style="text-align:center; background-color: #dddcdc" colspan="7">
            <b>RECEPTOR</b>
        </td>
    </tr>
    <tr>
        <td style="text-align:left;" colspan="7">

            <b>Nombre o razón social:</b> '.$empresa["nombre"].'<br>
            <b>NIT:</b> '.$empresa["nit"].'<br>
            <b>NRC:</b> '.$empresa["nrc"].'<br>
            <b>Actividad Económica:</b> '.$empresa["desActividad"].'<br>
            <b>Dirección:</b> '.$departamentos[$empresa["departamento"]] . ', ' .$municipios[$empresa["departamento"]][$empresa["municipio"]] . ', ' .$empresa["direccion"].'<br>
            <b>Número de teléfono:</b> '.$empresa["telefono"].'<br>
            <b>Correo Electrónico:</b> '.$empresa["correo"].'<br>
            <b>Vendedor:</b> '.$nombreVendedor.'<br>
            <b>Facturador:</b> '.$nombreFacturador.'<br>
            <br>
        </td>
        <td style="text-align:left" colspan="7">

            <b>Nombre o razón social:</b> '.$cliente["nombre"].'<br>
            <b>NIT:</b> '.$cliente["NIT"].'<br>
            <b>NRC:</b> '.$cliente["NRC"].'<br>
            <b>Actividad Económica:</b> '.$cliente["descActividad"].'<br>
            <b>Dirección:</b> '.$cliente["direccion"].'<br>
            <b>Número de teléfono:</b> '.$cliente["telefono"].'<br>
            <b>Correo Electrónico:</b> '.$cliente["correo"].'<br>
        </td>
        
    </tr>
    <tr>
        <td colspan="14" style="font-size: 6px"><p>1) Para la venta de máquinas nuevas, si el cliente no ha realizado el pago completo de la máquinas, esta continúa siendo propiedad de JSTAR SEWING SUPPLY, S.A. DE C.V. hasta que el cliente complete el pago correspondiente de la máquina.</p></td><br><br>
    </tr>
    <tr>
        <td colspan="14" style="font-size: 6px"><p>1) Favor hacer cheque a nombre de JSTAR SEWING SUPPLY, S.A. DE C.V. y poner sello de NO NEGOCIABLE 2) Devolución de partes y/o accesorios se aceptan solo dos días después de hecha la compra; partes dañadas no se aceptan. Partes y accesorios solicitados bajo orden de compra no podrán ser devueltos. 3) Se cobrará un interes mensual de 5% en Facturas vencidadas despues de 30 días. 4) Todos los clientes tienen tienen que cancelar sus cuentas en un periodo de 30 días. Todos los gastos incurridos durante litigación legal serán sumados a la cuenta del cliente. 5) Los cargos del banco por cheques rebotados serán reponsabilidad del cliente y su reemplazo tendrá que ser con cheque certificado o pago en efectivo.</p></td>
    </tr>
    
</table>
<hr>';


$condicionTexto = "";
if($factura["condicionOperacion"] == "1"){
    $condicionTexto = "Contado";
}
if($factura["condicionOperacion"] == "2"){
    $condicionTexto = "Crédito";
}
if($factura["condicionOperacion"] == "3"){
    $condicionTexto = "Otro";
}

$unidades = [
    "59" => "Unidad",
    "57" => "Ciento",
    "58" => "Docena",
    "1"  => "Metro",
    "2"  => "Yarda",
    "6"  => "Milímetro",
    "9"  => "Kilómetro cuadrado",
    "10" => "Hectárea",
    "13" => "Metro cuadrado",
    "15" => "Vara cuadrada",
    "18" => "Metro cúbico",
    "20" => "Barril",
    "22" => "Galón",
    "23" => "Litro",
    "24" => "Botella",
    "26" => "Mililitro",
    "30" => "Tonelada",
    "32" => "Quintal",
    "33" => "Arroba",
    "34" => "KG",
    "36" => "Libra",
    "37" => "Onza troy",
    "38" => "Onza",
    "39" => "Gramo",
    "40" => "Miligramo",
    "42" => "Megawatt",
    "43" => "Kilowatt",
    "44" => "Watt",
    "45" => "Megavoltio-amperio",
    "46" => "Kilovoltio-amperio",
    "47" => "Voltio-amperio",
    "49" => "Gigawatt-hora",
    "50" => "Megawatt-hora",
    "51" => "Kilowatt-hora",
    "52" => "Watt-hora",
    "53" => "Kilovoltio",
    "54" => "Voltio",
    "55" => "Millar",
    "56" => "Medio millar",
    "99" => "Otra"
];


if($factura["tipoDte"] === "01" && $cliente["tipo_cliente"] === "00"){// Factura, Persona normal y declarante de IVA
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei) {
            $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioConIva"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioConIva"] - $producto["descuentoConIva"]) * $producto["cantidad"]), 4, '.', ',').'</td>                    
                    </tr>
                    
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                     </tr>   ';

        }
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["total"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "01" && $cliente["tipo_cliente"] == "01"){// Factura, Persona normal y declarante de IVA
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioConIva"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioConIva"] - $producto["descuentoConIva"]) * $producto["cantidad"]), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["total"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["total"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "01" && $cliente["tipo_cliente"] == "02"){// Factura, Empresa con beneficios fiscales
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]), 4, '.', ',').'</td>                    
                </tr>
                
        ';
        $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["totalSinIva"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "01" && $cliente["tipo_cliente"] == "03"){// Factura, Diplomáticos
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]), 4, '.', ',').'</td>                    
                </tr>
                
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["totalSinIva"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "03" && $cliente["tipo_cliente"] == "01"){// CCF, Declarantes de IVA
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                    
                </tr>
                
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $retencionGranContribuyente = 0.0;
    if($factura["gran_contribuyente"] == "Si"){
        $retencionGranContribuyente = round(($factura["totalSinIva"] * 0.01), 2);
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $'.number_format((($factura["total"] - $factura["totalSinIva"])), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 1%:</b> $'.$retencionGranContribuyente.'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["total"] - $retencionGranContribuyente), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["total"] - $retencionGranContribuyente), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["total"] - $retencionGranContribuyente).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "03" && $cliente["tipo_cliente"] == "02"){// CCF, Empresa con beneficios fiscales
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                    
                </tr>
                
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB:</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $'.$factura["totalSinIva"].'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["totalSinIva"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "03" && $cliente["tipo_cliente"] == "03"){// CCF, Diplomáticos
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                </tr>
                
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sub-Total:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IVA 13%:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retención Renta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["totalSinIva"]).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "11" && ($cliente["tipo_cliente"] == "01" || $cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03")){// Exportación, Declarantes de IVA
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei) {
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                </tr>
            ';
            $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                     </tr>   ';

        }
        
    }
    $totalOpera = $factura["flete"] + $factura["seguro"] + $factura["totalSinIva"];
    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
            
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] != "No"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Flete:</b> $'.number_format(($factura["flete"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Seguro:</b> $'.number_format(($factura["seguro"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Monto total de la operación:</b> $'.number_format(($totalOpera), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalOpera), 4, '.', ',').'<br>
                </p>
                
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "14" && $cliente["tipo_cliente"] == "00"){// Sujeto no excluido, persona normal
    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>
                </tr>
                
        ';
        $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] == "Si"){
                    $html .= '<b>Termino de venta FOB</b>';
                }
                
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sumas:</b> $'.number_format(($factura["totalSinIva"]), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Renta retenida:</b> $'.number_format((($factura["totalSinIva"] * 0.10)), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total:</b> $'.number_format(($factura["totalSinIva"]-($factura["totalSinIva"] * 0.10)), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($factura["totalSinIva"]-($factura["totalSinIva"] * 0.10)), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($factura["totalSinIva"]-($factura["totalSinIva"] * 0.10)).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "05" && $cliente["tipo_cliente"] == "01"){// Nota de crédito, Declarantes de IVA
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));

            $totalProD = (($producto["descuento"] * $producto["cantidad"]));
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
            // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["descuento"] * $producto["cantidad"])), 4, '.', ',').'</td>                    
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $'.number_format(($totalGravado * 0.13), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado + ($totalGravado * 0.13)), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado + ($totalGravado * 0.13)).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "05" && $cliente["tipo_cliente"] == "02"){// Nota de crédito, Beneficios fiscales
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta no sujeta</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));
    
            $totalProD = (($producto["descuento"] * $producto["cantidad"]));
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"] * $producto["cantidad"]), 4, '.', ',').'</td>                    
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.00<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "05" && $cliente["tipo_cliente"] == "03"){// Nota de crédito, Diplomático
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta exenta</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));

            $totalProD = (($producto["descuento"] * $producto["cantidad"]));
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
            // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["descuento"] * $producto["cantidad"])), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.00<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "06" && $cliente["tipo_cliente"] == "01"){// Nota de dédito, Declarantes de IVA
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Aumento</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));
    
            $totalProD = ($producto["descuento"] * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["descuento"] * $producto["cantidad"])), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $'.number_format(($totalGravado * 0.13), 4, '.', ',').'<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado + ($totalGravado * 0.13)), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado + ($totalGravado * 0.13)).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "06" && $cliente["tipo_cliente"] == "02"){// Nota de dédito, beneficios fiscales
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Aumento</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta no sujeta</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));

            $totalProD = ($producto["descuento"] * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
            // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["descuento"] * $producto["cantidad"])), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> '.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "06" && $cliente["tipo_cliente"] == "03"){// Nota de dédito, Diplomáticos
    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);

    $html .= '<br><br> Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Unidad</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="7">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Aumento</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta exenta</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        if($producto["descuento"] != "0"){
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            $des = $producto["descuento"];
            $desR = floatval(number_format($des, 4, '.', ''));
    
            $totalProD = ($producto["descuento"] * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">Unidad</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="7">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["descuento"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["descuento"] * $producto["cantidad"])), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>
            </td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta no sujeta:</b> '.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}
if($factura["idFacturaRelacionada"] != ""){

    $item = "id";
    $orden = "id";
    $valor = $factura["idFacturaRelacionada"];
    $optimizacion = "no";

    $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
    
    if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "01" && $facturaOriginal["tipoDte"] == "03"){// Nota de remisión, ccf contribuyente
    

        $html .= 'Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
        <tr>
    
            <td style="text-align:center; background-color: #abebff" colspan="1">
                <br><br><b>N°</b><br>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Código</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Origen</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Peso</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Marca</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Modelo</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="11">
                <br><br><b>Descripción</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Cant.</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Precio unitario</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Venta gravada</b>
            </td>
        </tr>';
    
        // Decodificar los productos de la factura
        $productos = json_decode($facturaOriginal["productos"], true); // true para obtener un array asociativo
        $contador = 1;
        $totalGravado = 0.0;
        // Recorrer cada producto y mapear los datos
        foreach ($productos as $producto) {
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            
            $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
    
        $html .= '</table>
        <br><br>
        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
    
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>DETALLES</b>
                </td>
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>SUMA TOTAL DE OPERACIONES</b>
                </td>
            </tr>
            <tr>
                <td style="text-align:left;" colspan="7">
                    <br><br>
                    
                    <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                    if($factura["venta_cif"] != ""){
                        $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                    }
                    if($factura["venta_fob"] != "No"){
                        $html .= '<b>Termino de venta FOB</b>';
                    }
    
                    if($factura["idMotorista"] != ""){
                        $item = "id";
                        $valor = $factura["idMotorista"];
                        $orden = "id";
                        $motorista = ControladorClientes::ctrMostrarMotoristas($item, $valor, $orden);
    
                        $html .= '<b>Nombre: </b>'.$motorista["nombre"].'<b> Placa: </b>'.$motorista["placaMotorista"].'<br>';
                    }
    
                    if($factura["arancel"] != ""){
                        $html .= '<b>Arancel</b>: '.$factura["arancel"].'<br>';
                    }
                    if($factura["periodo"] != ""){
                        $html .= '<b>Periodo</b>: '.$factura["periodo"].'<br>';
                    }
                    
                $html .= '</td>
                <td style="text-align:left" colspan="7">
                    <br><br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> '.number_format(($totalGravado), 4, '.', ',').'<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $'.number_format(($totalGravado*0.13), 4, '.', ',').'<br>
                    <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                        <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado+($totalGravado*0.13)), 4, '.', ',').'<br>
                    </p>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado + ($totalGravado * 0.13)).':</b>
                    <br>
                </td>
            </tr>
        </table>';
    }
    
    if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "02" && $facturaOriginal["tipoDte"] == "03"){// Nota de remisión, ccf beneficios
        $item = "id";
        $orden = "id";
        $valor = $factura["idFacturaRelacionada"];
        $optimizacion = "no";
    
        $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
    
        $html .= 'Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
        <tr>
    
            <td style="text-align:center; background-color: #abebff" colspan="1">
                <br><br><b>N°</b><br>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Código</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Origen</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Peso</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Marca</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Modelo</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="11">
                <br><br><b>Descripción</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Cant.</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Precio unitario</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Venta gravada</b>
            </td>
        </tr>';
    
        // Decodificar los productos de la factura
        $productos = json_decode($facturaOriginal["productos"], true); // true para obtener un array asociativo
        $contador = 1;
        $totalGravado = 0.0;
        // Recorrer cada producto y mapear los datos
        foreach ($productos as $producto) {
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            
            $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
    
        $html .= '</table>
        <br><br>
        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
    
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>DETALLES</b>
                </td>
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>SUMA TOTAL DE OPERACIONES</b>
                </td>
            </tr>
            <tr>
                <td style="text-align:left;" colspan="7">
                    <br><br>
                    
                    <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] != "No"){
                    $html .= '<b>Termino de venta FOB</b>';
                }

                if($factura["idMotorista"] != ""){
                    $item = "id";
                    $valor = $factura["idMotorista"];
                    $orden = "id";
                    $motorista = ControladorClientes::ctrMostrarMotoristas($item, $valor, $orden);

                    $html .= '<b>Nombre: </b>'.$motorista["nombre"].'<b> Placa: </b>'.$motorista["placaMotorista"].'<br>';
                }

                if($factura["arancel"] != ""){
                    $html .= '<b>Arancel</b>: '.$factura["arancel"].'<br>';
                }
                if($factura["periodo"] != ""){
                    $html .= '<b>Periodo</b>: '.$factura["periodo"].'<br>';
                }
                
            $html .= '</td>
                <td style="text-align:left" colspan="7">
                    <br><br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                    <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                        <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    </p>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                    <br>
                </td>
            </tr>
        </table>';
    }
    
    if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "03" && $facturaOriginal["tipoDte"] == "03"){// Nota de remisión, ccf diplomas
        $item = "id";
        $orden = "id";
        $valor = $factura["idFacturaRelacionada"];
        $optimizacion = "no";
    
        $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
    
        $html .= 'Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
        <tr>
    
            <td style="text-align:center; background-color: #abebff" colspan="1">
                <br><br><b>N°</b><br>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Código</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Origen</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Peso</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Marca</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Modelo</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="11">
                <br><br><b>Descripción</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Cant.</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Precio unitario</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Venta gravada</b>
            </td>
        </tr>';
    
        // Decodificar los productos de la factura
        $productos = json_decode($facturaOriginal["productos"], true); // true para obtener un array asociativo
        $contador = 1;
        $totalGravado = 0.0;
        // Recorrer cada producto y mapear los datos
        foreach ($productos as $producto) {
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            
            $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
    
        $html .= '</table>
        <br><br>
        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
    
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>DETALLES</b>
                </td>
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>SUMA TOTAL DE OPERACIONES</b>
                </td>
            </tr>
            <tr>
                <td style="text-align:left;" colspan="7">
                    <br><br>
                    
                    <b>Condición de la operación:</b> '.$condicionTexto.'<br>
                </td>
                <td style="text-align:left" colspan="7">
                    <br><br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                    <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                        <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    </p>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                    <br>
                </td>
            </tr>
        </table>';
    }
    
    if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "01" && $facturaOriginal["tipoDte"] == "11"){// Nota de remisión, export contribuyente
        $item = "id";
        $orden = "id";
        $valor = $factura["idFacturaRelacionada"];
        $optimizacion = "no";
    
        $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
    
        $html .= 'Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
        <tr>
    
            <td style="text-align:center; background-color: #abebff" colspan="1">
                <br><br><b>N°</b><br>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Código</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Origen</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Peso</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Marca</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Modelo</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="11">
                <br><br><b>Descripción</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Cant.</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Precio unitario</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Venta gravada</b>
            </td>
        </tr>';
    
        // Decodificar los productos de la factura
        $productos = json_decode($facturaOriginal["productos"], true); // true para obtener un array asociativo
        $contador = 1;
        $totalGravado = 0.0;
        // Recorrer cada producto y mapear los datos
        foreach ($productos as $producto) {
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            
            $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
    
        $html .= '</table>
        <br><br>
        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
    
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>DETALLES</b>
                </td>
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>SUMA TOTAL DE OPERACIONES</b>
                </td>
            </tr>
            <tr>
                <td style="text-align:left;" colspan="7">
                    <br><br>
                    
                    <b>Condición de la operación:</b> '.$condicionTexto.'<br>
                </td>
                <td style="text-align:left" colspan="7">
                    <br><br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                    <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                        <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    </p>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                    <br>
                </td>
            </tr>
        </table>';
    }
    
    if($factura["tipoDte"] == "04" && ($cliente["tipo_cliente"] == "02" || $cliente["tipo_cliente"] == "03") && $facturaOriginal["tipoDte"] == "11"){// Nota de remisión, export beneficios diplomas
        $item = "id";
        $orden = "id";
        $valor = $factura["idFacturaRelacionada"];
        $optimizacion = "no";
    
        $facturaOriginal = ControladorFacturas::ctrMostrarFacturas($item, $valor, $orden, $optimizacion);
    
        $html .= 'Código de generación de factura que afecta: '.$facturaOriginal["codigoGeneracion"].'<br><br><table border="0" cellspacing="0" cellpadding="2">
        <tr>
    
            <td style="text-align:center; background-color: #abebff" colspan="1">
                <br><br><b>N°</b><br>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Código</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Origen</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Peso</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Marca</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="4">
                <br><br><b>Modelo</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="11">
                <br><br><b>Descripción</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="2">
                <br><br><b>Cant.</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Precio unitario</b>
            </td>
            <td style="text-align:center; background-color: #abebff" colspan="3">
                <br><br><b>Venta gravada</b>
            </td>
        </tr>';
    
        // Decodificar los productos de la factura
        $productos = json_decode($facturaOriginal["productos"], true); // true para obtener un array asociativo
        $contador = 1;
        $totalGravado = 0.0;
        // Recorrer cada producto y mapear los datos
        foreach ($productos as $producto) {
            $item = "id";
            $valor = $producto["idProducto"];
        
            $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);
    
            
            $totalProD = ($producto["precioSinImpuestos"] * $producto["cantidad"]);
            $totalProF = floatval(number_format($totalProD, 4, '.', ''));
             // Alternar color de fondo
            $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
            if($productoLei){
                $html .= '
                    <tr style="background-color: '.$bgColor.'">
                        <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                        <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] * $producto["cantidad"])), 4, '.', ',').'</td>
                    </tr>
                    
            ';
            $totalGravado += $totalProF;
            $contador++;
            } else {
                $html .= '<tr style="background-color: '.$bgColor.'">
                                <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                        </tr>   ';
    
            }
            
        }
    
        $html .= '</table>
        <br><br>
        <table border="0" cellspacing="0" cellpadding="2">
            <tr>
    
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>DETALLES</b>
                </td>
                <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                    <b>SUMA TOTAL DE OPERACIONES</b>
                </td>
            </tr>
            <tr>
                <td style="text-align:left;" colspan="7">
                    <br><br>
                    
                    <b>Condición de la operación:</b> '.$condicionTexto.'<br>
                </td>
                <td style="text-align:left" colspan="7">
                    <br><br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                    <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                        <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                    </p>
                    <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                    <br>
                </td>
            </tr>
        </table>';
    }
}

if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "02"){// Nota de remisión, ccf beneficios

    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

        
        $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
        $totalProF = floatval(number_format($totalProD, 4, '.', ''));
         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                </tr>
                
        ';
        $totalGravado += $totalProF;
        $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] != "No"){
                    $html .= '<b>Termino de venta FOB</b>';
                }

                if($factura["idMotorista"] != ""){
                    $item = "id";
                    $valor = $factura["idMotorista"];
                    $orden = "id";
                    $motorista = ControladorClientes::ctrMostrarMotoristas($item, $valor, $orden);

                    $html .= '<b>Nombre: </b>'.$motorista["nombre"].'<b> Placa: </b>'.$motorista["placaMotorista"].'<br>';
                }

                if($factura["arancel"] != ""){
                    $html .= '<b>Arancel</b>: '.$factura["arancel"].'<br>';
                }
                if($factura["periodo"] != ""){
                    $html .= '<b>Periodo</b>: '.$factura["periodo"].'<br>';
                }
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "03"){// Nota de remisión, ccf diplomas


    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

        
        $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
        $totalProF = floatval(number_format($totalProD, 4, '.', ''));
         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                </tr>
                
        ';
        $totalGravado += $totalProF;
        $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] != "No"){
                    $html .= '<b>Termino de venta FOB</b>';
                }

                if($factura["idMotorista"] != ""){
                    $item = "id";
                    $valor = $factura["idMotorista"];
                    $orden = "id";
                    $motorista = ControladorClientes::ctrMostrarMotoristas($item, $valor, $orden);

                    $html .= '<b>Nombre: </b>'.$motorista["nombre"].'<b> Placa: </b>'.$motorista["placaMotorista"].'<br>';
                }

                if($factura["arancel"] != ""){
                    $html .= '<b>Arancel</b>: '.$factura["arancel"].'<br>';
                }
                if($factura["periodo"] != ""){
                    $html .= '<b>Periodo</b>: '.$factura["periodo"].'<br>';
                }
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Iva 13%:</b> $0.0<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

if($factura["tipoDte"] == "04" && $cliente["tipo_cliente"] == "01"){// Nota de remisión, export contribuyente

    $html .= '<table border="0" cellspacing="0" cellpadding="2">
    <tr>

        <td style="text-align:center; background-color: #abebff" colspan="1">
            <br><br><b>N°</b><br>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Código</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Origen</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Peso</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Marca</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="4">
            <br><br><b>Modelo</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="11">
            <br><br><b>Descripción</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="2">
            <br><br><b>Cant.</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Precio unitario</b>
        </td>
        <td style="text-align:center; background-color: #abebff" colspan="3">
            <br><br><b>Venta gravada</b>
        </td>
    </tr>';

    // Decodificar los productos de la factura
    $productos = json_decode($factura["productos"], true); // true para obtener un array asociativo
    $contador = 1;
    $totalGravado = 0.0;
    // Recorrer cada producto y mapear los datos
    foreach ($productos as $producto) {
        $item = "id";
        $valor = $producto["idProducto"];
    
        $productoLei = ControladorProductos::ctrMostrarProductos($item, $valor);

        
        $totalProD = (($producto["precioSinImpuestos"] - $producto["descuento"]) * $producto["cantidad"]);
        $totalProF = floatval(number_format($totalProD, 4, '.', ''));
         // Alternar color de fondo
        $bgColor = ($contador % 2 == 0) ? '#dddcdc' : '#ffffff';
        if($productoLei){
            $html .= '
                <tr style="background-color: '.$bgColor.'">
                    <td style="border: 1px solid black; text-align:center;" colspan="1">'.$contador.'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["codigo"].'</td>';
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["origen"].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="2">'.$productoLei["origen"].'</td>';
                    }
                    if(isset($producto["origen"])){
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$producto["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    } else {
                        $html .= '<td style="border: 1px solid black; text-align:center;" colspan="3">'.$productoLei["peso"].' '.$unidades[$productoLei["unidadMedida"]].'</td>';
                    }
                    
                    
                    $html .= '<td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["marca"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="4">'.$producto["modelo"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="11">'.$productoLei["nombre"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="2">'.$producto["cantidad"].'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format(($producto["precioSinImpuestos"]), 4, '.', ',').'</td>
                    <td style="border: 1px solid black; text-align:center;" colspan="3">$'.number_format((($producto["precioSinImpuestos"] - $producto["descuento"] ) * $producto["cantidad"]), 4, '.', ',').'</td>                                        
                </tr>
                
        ';
        $totalGravado += $totalProF;
        $contador++;
        } else {
            $html .= '<tr style="background-color: '.$bgColor.'">
                            <td style="border: 1px solid black; text-align:center;" colspan="100">Producto eliminado</td>
                    </tr>   ';

        }
        
    }

    $html .= '</table>
    <br><br>
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>

            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>DETALLES</b>
            </td>
            <td style="text-align:left; background-color: #abebff; border: 1px solid black" colspan="7">
                <b>SUMA TOTAL DE OPERACIONES</b>
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="7">
                <br><br>
                
                <b>Condición de la operación:</b> '.$condicionTexto.'<br>';
                if($factura["venta_cif"] != ""){
                    $html .= '<b>Termino de venta CIF</b>: '.$factura["venta_cif"].'<br>';
                }
                if($factura["venta_fob"] != "No"){
                    $html .= '<b>Termino de venta FOB</b>';
                }

                if($factura["idMotorista"] != ""){
                    $item = "id";
                    $valor = $factura["idMotorista"];
                    $orden = "id";
                    $motorista = ControladorClientes::ctrMostrarMotoristas($item, $valor, $orden);

                    $html .= '<b>Nombre: </b>'.$motorista["nombre"].'<b> Placa: </b>'.$motorista["placaMotorista"].'<br>';
                }

                if($factura["arancel"] != ""){
                    $html .= '<b>Arancel</b>: '.$factura["arancel"].'<br>';
                }
                if($factura["periodo"] != ""){
                    $html .= '<b>Periodo</b>: '.$factura["periodo"].'<br>';
                }
                
            $html .= '</td>
            <td style="text-align:left" colspan="7">
                <br><br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta gravada:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venta exenta:</b> $0.00<br>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total gravada:</b> $0.0<br>
                <p style="background-color: #abebff; line-height: 5px; text-align: left;">
                    <b><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total a pagar:</b> $'.number_format(($totalGravado), 4, '.', ',').'<br>
                </p>
                <b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.numeroAmoneda($totalGravado).':</b>
                <br>
            </td>
        </tr>
    </table>';
}

$html .= '
    <table border="0" cellspacing="0" cellpadding="2">
    <br>
    <tr>
            <td style="text-align:left;" colspan="14">
                Bodega&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Inspección&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Import/Export&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Motorista&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Import/Export&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Bodega&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Contabilidad
            </td>
        </tr>
        <tr>
            <td style="text-align:left;" colspan="14">
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                ______________
            </td>
        </tr>

    </table>';



$pdf->writeHTML($html, true, false, true, false, '');


// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('Factura '.$factura["codigoGeneracion"].'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
}
