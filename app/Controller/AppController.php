<?php
  define('COMPANY_NAME','Uno');
	//define('COMPANY_URL','www.ornasa.com');
	define('COMPANY_MAIL','gerencia@ornasa.com');
	define('COMPANY_ADDRESS','Managua');
	define('COMPANY_PHONE','');
	define('COMPANY_RUC','');
  
  define('ENTERPRISE_LAS_PALMAS','1');
  define('ENTERPRISE_TEST','2');
  
  define('UNIT_GALLONS','1');
  define('UNIT_LITERS','2');
  define('GALLONS_TO_LITERS','3.785400');
  
	define('CURRENCY_CS','1');
	define('CURRENCY_USD','2');
	
	define('ROLE_ADMIN','4');
	define('ROLE_ASSISTANT','5');
	define('ROLE_FOREMAN','6');
	define('ROLE_MANAGER','7');
	define('ROLE_SALES','8');
  define('ROLE_ACCOUNTING','9');
  define('ROLE_CLIENT','10');
	
	define('NA','N/A');
		
	define('MOVEMENT_SALE','1');
  define('MOVEMENT_PURCHASE','2');
	define('MOVEMENT_ADJUSTMENT_GENERAL','11');
  define('MOVEMENT_ADJUSTMENT_CALIBRATION','12');
  define('MOVEMENT_ADJUSTMENT_MEASURE','13');
  define('MOVEMENT_PURCHASE_CONSUMIBLES','100');
	
  define('PROVIDER_UNO','64');
  
  define('CATEGORY_FUELS','1');
	define('CATEGORY_PRODUCTS','2');
	define('CATEGORY_SERVICES','3');
  
  define('PRODUCT_TYPE_FUELS','1');
	define('PRODUCT_TYPE_LUBRICANTS','2');
	define('PRODUCT_TYPE_GROCERIES','3');
  define('PRODUCT_TYPE_SERVICES','4');
  
	define('HOLIDAY_TYPE_SOLICITADO','1');
	define('HOLIDAY_TYPE_PROGRAMADO','2');
	define('HOLIDAY_TYPE_AUSENCIA_LABORAL','3');
	define('HOLIDAY_TYPE_FERIADO','4');
	
	define('CASH_RECEIPT_TYPE_CREDIT','1');
	define('CASH_RECEIPT_TYPE_OTHER','3');
  
  define('PAYMENT_MODE_CASH','1'); 
	define('PAYMENT_MODE_CARD_BAC','2'); 
  define('PAYMENT_MODE_CREDIT','3'); 
	define('PAYMENT_MODE_CARD_BANPRO','4'); 
  
  define('CLIENTS_VARIOUS','9');
  
  define('SHIFT_MORNING','1');
  define('SHIFT_AFTERNOON','2');
  define('SHIFT_NIGHT','3');
	
	define('ACCOUNTING_CODE_CASHBOXES','4'); // accounting code 101-001
	define('ACCOUNTING_CODE_CASHBOX_MAIN','5'); // accounting code 101-001-001
	define('ACCOUNTING_CODE_BANKS','11'); // accounting code 101-003
	define('ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES','17'); // accounting code 101-004-001
	define('ACCOUNTING_CODE_INVENTORY','29'); // accounting code 101-005
	define('ACCOUNTING_CODE_INVENTORY_RAW_MATERIAL','91'); // accounting code 101-005-001
	define('ACCOUNTING_CODE_INVENTORY_FINISHED_PRODUCT','92'); // accounting code 101-005-002
	define('ACCOUNTING_CODE_INVENTORY_OTHER_MATERIAL','93'); // accounting code 101-005-003
	define('ACCOUNTING_CODE_PROVIDERS','34'); // accounting code 201-001
	define('ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR','50'); // accounting code 401
	define('ACCOUNTING_CODE_INGRESOS_VENTA','89'); // accounting code 401-001
	define('ACCOUNTING_CODE_INGRESOS_DESCUENTOS','55'); // accounting code 402
	define('ACCOUNTING_CODE_INGRESOS_OTROS','58'); // accounting code 403	
	define('ACCOUNTING_CODE_COSTS','60'); // accounting code 500
	define('ACCOUNTING_CODE_COSTOS_VENTA','61'); // accounting code 501
	define('ACCOUNTING_CODE_SPENDING_OPERATIONS','64'); // accounting code 600
	define('ACCOUNTING_CODE_GASTOS_ADMIN','65'); // accounting code 601
	define('ACCOUNTING_CODE_GASTOS_VENTA','73'); // accounting code 602
	define('ACCOUNTING_CODE_GASTOS_FINANCIEROS','74'); // accounting code 603
	define('ACCOUNTING_CODE_GASTOS_PRODUCCION','79'); // accounting code 604
	define('ACCOUNTING_CODE_GASTOS_OTROS','75'); // accounting code 605
	
	define('ACCOUNTING_CODE_RETENCIONES_POR_COBRAR','85'); // accounting code 101-004-004
	define('ACCOUNTING_CODE_IVA_POR_PAGAR','84'); // accounting code 201-002-3
	define('ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS','59'); // accounting code 403-001
	define('ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA','88'); // accounting code 403-002
	define('ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA','86'); // accounting code 602-002
	define('ACCOUNTING_CODE_GASTO_DIFERENCIA_CAMBIARIA','87'); // accounting code 603-001
	
	define('ACCOUNTING_CODE_BANKS_CS','12'); // accounting code 101-003-001
	define('ACCOUNTING_CODE_BANKS_USD','14'); // accounting code 101-003-002
	
	define('ACCOUNTING_CODE_BANK_CS','83'); // accounting code 101-003-001-001
	define('ACCOUNTING_CODE_BANK_USD','153'); // accounting code 101-003-002-001
	
	define('ACCOUNTING_CODE_ACTIVOS','1'); // accounting code 100
	define('ACCOUNTING_CODE_PASIVOS','32'); // accounting code 200
	
	define('ACCOUNTING_REGISTER_TYPE_CD','2'); 
	define('ACCOUNTING_REGISTER_TYPE_CP','3'); 
	
	define('MAX_ROWS','30'); 

App::uses('Controller', 'Controller');

class AppController extends Controller {

	public $components = array(	
		'Session',
		//'DebugKit.Toolbar',
		'Acl',
        'Auth' => array(
            'authorize' => array(
                'Actions' => array('actionPath' => 'controllers')
            )
        ),
		//'AclMenu.Menu'
		/******* for original auth use
		'Auth' => array(
            'loginRedirect' => array(
                'controller' => 'locations',
                'action' => 'display',
				'home'
            ),
            'logoutRedirect' => array(
                'controller' => 'users',
                'action' => 'login'
            ),
			'authorize' =>  array('Controller')
        )
		*****************/
	);
	public $helpers = array( 
		'Html', 
		'Form', 
		'Session',
		'MenuBuilder.MenuBuilder' => array(
			'authVar' => 'user',
			'authModel' => 'User',
			'authField' => 'role_id',
		),
	);
	
	function recordUserActivity($userName,$userEvent){
		$this->request->data['UserLog']['user_id'] = $this->Auth->User('id');;
		$this->request->data['UserLog']['username'] = $userName;
		$this->request->data['UserLog']['event'] = $this->normalizeChars($userEvent);
		$this->request->data['UserLog']['created'] = date("Y-m-d H:i:s");
		
		$this->loadModel('UserLog');
		$this ->UserLog->create();
		$this->UserLog->save($this->request->data);
	}
	
	function recordUserAction($item_id=null,$action_name=null,$controller_name=null){
		
		if ($item_id==null){
			$item_id=0;
			if (!empty($this->params['pass'])){
				$item_id=$this->params['pass']['0'];
			}
		}
		if ($action_name==null){
			$action_name= $this->params['action'];
		}
		if ($controller_name==null){
			$controller_name= $this->params['controller'];
		}
		
		$this->loadModel('UserAction');
		$userActionData=array();
		$userActionData['UserAction']['user_id']=$this->Auth->User('id');
		$userActionData['UserAction']['controller_name']=$controller_name;
		$userActionData['UserAction']['action_name']=$action_name;
		$userActionData['UserAction']['item_id']=$item_id;
		$userActionData['UserAction']['action_datetime']= date("Y-m-d H:i:s");
		$this ->UserAction->create();
		$this->UserAction->save($userActionData);		
	}
		
  public function beforeFilter() {
		//Configure AuthComponent
    
    $this->Auth->authError = "No tiene permiso para ver este funcionalidad";
        $this->Auth->loginAction = array(
          'controller' => 'users',
          'action' => 'login'
        );
        $this->Auth->logoutRedirect = array(
          'controller' => 'users',
          'action' => 'login'
        );
		$this->Auth->loginRedirect = array(
		  'controller' => 'stock_items',
		  'action' => 'index',
		  'home'
		);
		
		$user = $this->Auth->user();
		$this->set(compact('user'));
    //pr($user);
		
		
		
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $username = $this->Auth->User('username');
    $userhomepage = $this->userhome($userRoleId);
		$this->set(compact('username','userhomepage'));
		
    $this->loadModel('Constant');
    $companyNameConstant=$this->Constant->find('first',[
      'conditions'=>[
        'Constant.constant'=>'NOMBRE_COMPANIA'
      ]
    ]);
    if (!defined('COMPANY_NAME')){
      define('COMPANY_NAME',$companyNameConstant['Constant']['value']);
    }
		$this->loadModel('ExchangeRate');
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
		$this->set(compact('currentExchangeRate'));
    //pr($currentExchangeRate);
    //$this->Auth->allow();
		/*
		if ($this->Session->check('Config.language')) {
            Configure::write('Config.language', $this->Session->read('Config.language'));
        }
		*/
		
		// Define your menu for MenuBuilder
		
    $menu = array(
      'main-menu' => [
				[
          'title' => __('Registrar Informe'),
          'url' => ['controller' => 'orders', 'action' => 'registrarVentas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'salesregistration',
        ],
        [
          'title' => __('Reportes Informe'),
          'url' => ['controller' => 'orders', 'action' => 'reporteVentas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'salesreports',
        ],
        /*
        [
          'title' => __('Informe Diario'),
          'url' => ['controller' => 'orders', 'action' => 'registrarVentas'],
					'permissions'=>[ROLE_MANAGER,ROLE_SALES,ROLE_ACCOUNTING],
					'activesetter' => 'registrarventas',
        ],
        */
        [
          'title' => __('Inventory'),
          'url' => ['controller' => 'stockItems', 'action' => 'inventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_SALES,ROLE_ACCOUNTING],
					'activesetter' => 'inventory',
        ],
        [
          'title' => __('Entries'),
          'url' => ['controller' => 'orders', 'action' => 'resumenEntradas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'purchases',
        ],
				[
          'title' => __('Ingresos'),
          'url' => ['controller' => 'cashReceipts', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'finance',
        ],
				[
          'title' => __('Reportes'),
          'url' => ['controller' => 'stockItems', 'action' => 'estadoResultados'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'reports',
        ],
        [
          'title' => __('Configuration'),
          'url' =>['controller' => 'pages', 'action' => 'display','productionconfig'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'configuration',
        ],
      ],
      'sub-menu-sales-reports' => [
        [
          'title' => __('Informe Diario'),
          'url' => ['controller' => 'stockMovements', 'action' => 'informeDiario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'informediario',
        ],
        [
          'title' => __('Reporte de Ventas'),
          'url' => ['controller' => 'orders', 'action' => 'reporteVentas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'reporteventas',
        ],
				[
          'title' => __('Reporte Medidas de Tanque'),
          'url' => ['controller' => 'tankMeasurements', 'action' => 'reporteMedidasTanques'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reportemedidastanques',
        ],
        [
          'title' => __('Reporte Medidas de Manguera'),
          'url' => ['controller' => 'hoseMeasurements', 'action' => 'reporteMedidasMangueras'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reportemedidasmangueras',
        ],
        [
          'title' => __('Reporte de Recibos'),
          'url' => ['controller' => 'paymentReceipts', 'action' => 'reporteRecibos'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'reporterecibos',
        ],
      ],
      'sub-menu-sales-registration' => [
        [
          'title' => __('Informe I Ventas de Isla'),
          'url' => ['controller' => 'orders', 'action' => 'registrarVentas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'registrarventas',
        ],
        [
          'title' => __('Informe II Medidas de Tanque'),
          'url' => ['controller' => 'tankMeasurements', 'action' => 'registrarMedidas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'tankmeasurements',
        ],
        [
          'title' => __('Informe III Medidas de Manguera'),
          'url' => ['controller' => 'hoseMeasurements', 'action' => 'registrarMedidas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'hosemeasurements',
        ],
        [
          'title' => __('Informe IV Recibos'),
          'url' => ['controller' => 'paymentReceipts', 'action' => 'registrarRecibos'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'paymentreceipts',
        ],
        [
          'title' => __('Informe V Estimaciones de Compras'),
          'url' => ['controller' => 'movementEstimates', 'action' => 'reporteEstimacionesCompras'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'reporteestimacionescompras',
        ],
        [
          'title' => __('Facturas'),
          'url' => ['controller' => 'invoices', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'invoices',
        ],
				[
          'title' => __('Descuadre Subtotales Suma Productos'),
          'url' => ['controller' => 'orders', 'action' => 'resumenDescuadresSubtotalesSumaProductosVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'descuadresubtotalessumaproductos',
        ],
        /*
        [
          'title' => __('Descuadre Redondeo Totales'),
          'url' => ['controller' => 'orders', 'action' => 'resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'descuadreredondeototales',
        ],
        */
      ],
      'sub-menu-inventory' => [
				[
          'title' => __('Inventory'),
          'url' => ['controller' => 'stockItems', 'action' => 'inventario'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN, ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'inventory',
        ],
        [
          'title' => __('Ajustes Inventario'),
          'url' => ['controller' => 'stockMovements', 'action' => 'resumenAjustesInventario'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'adjustmentsInventory',
        ],
        [
          'title' => __('Registar Precios'),
          'url' => ['controller' => 'productPriceLogs', 'action' => 'registrarPrecios'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'registrarprecios',
        ],
        [
          'title' => __('Closing Dates'),
          'url' => ['controller' => 'closingDates', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'closingdates',
        ],
				/*
        [
          'title' => __('Reclassify Inventory'),
          'url' => array('controller' => 'stockItems', 'action' => 'resumenReclasificaciones'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'reclassification',
        ],
        */
				[
          'title' => __('Detalle Costo Producto'),
          'url' => array('controller' => 'stockItems', 'action' => 'detalleCostoProducto'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'detallecostoproducto',
        ],
        [
          'title' => __('cuadrar Estado de Lotes'),
          'url' => ['controller' => 'stockItems', 'action' => 'cuadrarEstadosDeLote'],
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'cuadrarestadodelotes',
        ],
      ],
      'sub-menu-entries' => [
        [
          'title' => __('Purchase Orders'),
          'url' => ['controller' => 'purchaseOrders', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_FOREMAN,ROLE_ACCOUNTING],
					'activesetter' => 'purchaseorders',
        ],
        [
          'title' => __('Entries'),
          'url' => ['controller' => 'orders', 'action' => 'resumenEntradas'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'entries',
        ],
        [
          'title' => 'Proveedores por Pagar',
          'url' => ['controller' => 'purchaseOrders', 'action' => 'verProveedoresPorPagar'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'proveedoresPorPagar',
        ],
      ],
			'sub-menu-finance' => [
        [
          'title' => __('Recibos de Caja'),
          'url' => ['controller' => 'cashReceipts', 'action' => 'resumen'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'cashreceipts',
        ],
        [
          'title' => __('Reporte Caja'),
          'url' => array('controller' => 'accountingCodes', 'action' => 'verReporteCaja'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
          'activesetter' => 'reportingresoscaja',
        ],
        [
          'title' => __('Cheques'),
          'url' => ['controller' => 'cheques', 'action' => 'index'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'cheques',
        ],
        [
          'title' => __('Depósitos'),
          'url' => ['controller' => 'transfers', 'action' => 'resumenDepositos'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'deposits',
        ],
        [
          'title' => __('Transferencias'),
          'url' => ['controller' => 'transfers', 'action' => 'index'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
          'activesetter' => 'transfers',
        ],
        [
          'title' => __('Estado de Cuentas'),
          'url' => ['controller' => 'invoices', 'action' => 'estadoCuentas'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
          'activesetter' => 'estadocuentas',
        ],
        [
          'title' => __('Clientes Por Cobrar'),
          'url' => array('controller' => 'invoices', 'action' => 'verClientesPorCobrar'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
          'activesetter' => 'reportclientesporcobrar',
        ],
        [
          'title' => __('Cobros de la Semana'),
          'url' => ['controller' => 'invoices', 'action' => 'verCobrosSemana'],
          'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
          'activesetter' => 'cobrossemana',
        ],
        
        [
          'title' => __('Historial de Pagos'),
          'url' => array('controller' => 'invoices', 'action' => 'verHistorialPagos'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
          'activesetter' => 'reporthistorialpagos',
        ],
        [
          'title' => __('Tasas de Cambio'),
          'url' => ['controller' => 'exchangeRates', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'exchangerates',
        ],
				/*
        [
          'title' => __('Contabilidad'),
          'url' => ['controller' => 'accountingRegisters', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
					'activesetter' => 'accounting',
					'children' => [
            [
              'title' => __('Comprobantes'),
              'url' => ['controller' => 'accountingRegisters', 'action' => 'index'],
              'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
              'activesetter' => 'accountingregisters',
            ],
            [
              'title' => __('Cuentas Contables'),
              'url' => ['controller' => 'accountingCodes', 'action' => 'index'],
              'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
              'activesetter' => 'accountingcodes',
            ],
            [
              'title' => __('Tipos de Comprobante'),
              'url' => ['controller' => 'accountingRegisterTypes', 'action' => 'index'],
              'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING],
              'activesetter' => 'accountingregistertypes',
            ],
            //[
							'title' => __('Estado Resultados Financieros'),
							'url' => array('controller' => 'accountingRegisters', 'action' => 'verEstadoResultados'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportestadoresultados',
						],
						[
							'title' => __('Balance General'),
							'url' => array('controller' => 'accountingRegisters', 'action' => 'verBalanceGeneral'),
							'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
							'activesetter' => 'reportbalancegeneral',
						],
					],
				],
        */
      ],
      'sub-menu-reports' => [
        [
          'title' => __('Estado de Resultados'),
          'url' => array('controller' => 'stockItems', 'action' => 'estadoResultados'),
          'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
          'activesetter' => 'reporteestadoresultados',
        ],
        [
          'title' => __('Reporte Salidas'),
          'url' => array('controller' => 'products', 'action' => 'viewSaleReport'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_ACCOUNTING),
					'activesetter' => 'reportesalidas',
        ],
				[
          'title' => __('Cierre'),
          'url' => ['controller' => 'orders', 'action' => 'verReporteCierre'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reportecierre',
        ],
				[
          'title' => __('Venta Producto por Cliente'),
          'url' => ['controller' => 'stockMovements', 'action' => 'verReporteVentaProductoPorCliente'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_MANAGER,ROLE_SALES, ROLE_ACCOUNTING],
					'activesetter' => 'reporteventaproductoporcliente',
        ],
      ],
			'sub-menu-configuration' => array(
				array(
          'title' => __('Tipos de Producto'),
          'url' => array('controller' => 'productTypes', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
					'activesetter' => 'producttypes',
        ),
				array(
          'title' => __('Productos'),
          'url' => array('controller' => 'products', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'products',
                ),
				array(
          'title' => __('Proveedores'),
          'url' => array('controller' => 'thirdParties', 'action' => 'resumenProveedores'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
					'activesetter' => 'providers',
        ),
				[
          'title' => __('Clientes'),
          'url' => ['controller' => 'thirdParties', 'action' => 'resumenClientes'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'clients',
        ],
        /*
        array(
          'title' => __('Asociar Clientes y Usuarios'),
          'url' => array('controller' => 'thirdParties', 'action' => 'asociarClientesUsuarios'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'asociarclientesusuarios',
        ),
				array(
          'title' => __('Reasignar Clientes'),
          'url' => array('controller' => 'thirdParties', 'action' => 'reasignarClientes'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT),
					'activesetter' => 'reasignarclientes',
        ),
        */
				[
          'title' => __('Hoses'),
          'url' => ['controller' => 'hoses', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'hoses',
        ],
        [
          'title' => __('Contadores Mangueras'),
          'url' => ['controller' => 'hoseCounters', 'action' => 'registrarContadores'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'hosecounters',
        ],
        [
          'title' => __('Islands'),
          'url' => ['controller' => 'islands', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'islands',
        ],
        [
          'title' => __('Tanks'),
          'url' => ['controller' => 'tanks', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING],
					'activesetter' => 'tanks',
        ],
				[
          'title' => __('Operadores'),
          'url' => ['controller' => 'operators', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'operators',
        ],
				array(
          'title' => __('Turnos'),
          'url' => array('controller' => 'shifts', 'action' => 'resumen'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
					'activesetter' => 'shifts',
        ),
				/*
        [
          'title' => __('Bodegas'),
          'url' => ['controller' => 'warehouses', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'warehouses',
        ],
        */
        [
          'title' => __('Usuarios'),
          'url' => ['controller' => 'users', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'users',
        ],
				/*
        [
          'title' => __('Papeles'),
          'url' => ['controller' => 'roles', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'roles',
        ],
        */
				array(
          'title' => __('Permisos de Usuarios'),
          'url' => array('controller' => 'users', 'action' => 'rolePermissions'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'rolepermissions',
        ),
        [
          'title' => __('Enterprises'),
          'url' => ['controller' => 'enterprises', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN,ROLE_ASSISTANT],
					'activesetter' => 'enterprises',
        ],
				array(
          'title' => __('Empleados'),
          'url' => array('controller' => 'employees', 'action' => 'resumen'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
					'activesetter' => 'employees',
        ),
				array(
          'title' => __('Días de Vacaciones'),
          'url' => array('controller' => 'employeeHolidays', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN),
					'activesetter' => 'employeeholidays',
        ),
				array(
          'title' => __('Motivos de Vacaciones'),
          'url' => array('controller' => 'holidayTypes', 'action' => 'index'),
					'permissions'=>array(ROLE_ADMIN,ROLE_ASSISTANT,ROLE_ACCOUNTING),
					'activesetter' => 'holidaytypes',
        ),
        [
          'title' => __('Constants'),
          'url' => ['controller' => 'constants', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'constants',
        ],
        [
          'title' => __('Units'),
          'url' => ['controller' => 'units', 'action' => 'resumen'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'units',
        ],
        [
          'title' => __('Payment Modes'),
          'url' => ['controller' => 'paymentModes', 'action' => 'index'],
					'permissions'=>[ROLE_ADMIN],
					'activesetter' => 'paymentmodes',
        ],
			),
    );
		$currentController= $this->params['controller'];
		$currentAction= $this->params['action'];
		$currentParameter=0;
		if (!empty($this->params['pass'])){
		
			$currentParameter=$this->params['pass']['0'];
		}
		/*
		//pr($this->params);
		echo "controller is ".$currentController."<br/>";
		echo "action is ".$currentAction."<br/>";
		echo "parameter is ".$currentParameter."<br/>";
		*/
		$sub="NA";
		$activeMenu="NA";
		$activeSub="NA";
		$activeSecond="NA";
		if (($currentAction=="index"||$currentAction=="view"||$currentAction=="add"||$currentAction=="edit") &&($currentController!="orders"&&$currentController!="thirdParties")){
			switch($currentController){
				case "purchaseOrders": 
					$activeMenu="purchases";
					$activeSub="purchaseorders";
					$sub="sub-menu-entries";
					break;
        case "cheques": 
					$activeMenu="finance";
					$activeSub="cheques";
					$sub="sub-menu-finance";
					break;
				/*case "cheque_types": 
					$activeMenu="finance";
					$activeSub="chequetypes";
					$sub="sub-menu-finance";
					break;*/
				case "transfers": 
					$activeMenu="finance";
					$activeSub="transfers";
					$sub="sub-menu-finance";
					break;
				case "exchangeRates": 
					$activeMenu="finance";
					$activeSub="exchangerates";
					$sub="sub-menu-finance";
					break;
				case "accountingCodes": 
					$activeMenu="finance";
					$activeSub="accountingcodes";
					$sub="sub-menu-finance";
					break;
				case "accountingRegisterTypes": 
					$activeMenu="finance";
					$activeSub="accountingregistertypes";
					$sub="sub-menu-finance";
					break;
				case "accountingRegisters": 
					$activeMenu="finance";
					$activeSub="accountingregisters";
					$sub="sub-menu-finance";
					break;
				case "productTypes": 
					$activeMenu="configuration";
					$activeSub="producttypes";
					$sub="sub-menu-configuration";
					break;
				case "products": 
					$activeMenu="configuration";
					$activeSub="products";
					$sub="sub-menu-configuration";
					break;
				/*
				case "warehouses": 
					$activeMenu="configuration";
					$activeSub="warehouses";
					$sub="sub-menu-configuration";
					break;
        */
				case "users": 
					$activeMenu="configuration";
					$activeSub="users";
					$sub="sub-menu-configuration";
					break;
				case "roles": 
					$activeMenu="configuration";
					$activeSub="roles";
					$sub="sub-menu-configuration";
					break;	
				case "employeeHolidays": 
					$activeMenu="configuration";
					$activeSub="employeeholidays";
					$sub="sub-menu-configuration";
					break;	
				case "holidayTypes": 
					$activeMenu="configuration";
					$activeSub="holidaytypes";
					$sub="sub-menu-configuration";
					break;	
        case "constants": 
					$activeMenu="configuration";
					$activeSub="constants";
					$sub="sub-menu-configuration";
					break;	  
        case "paymentModes": 
					$activeMenu="configuration";
					$activeSub="paymentmodes";
					$sub="sub-menu-configuration";
					break;  
        case "purchaseEstimations": 
					$activeMenu="purchaseestimations";
					//$activeSub="holidaytypes";
					//$sub="sub-menu-configuration";
					break;	
				case "client_requests": 
					$activeMenu="clientrequests";
					//$activeSub="holidaytypes";
					//$sub="sub-menu-configuration";
					break;	
			}
		}
    else if (($currentAction=="resumen"||$currentAction=="crear"||$currentAction=="editar"||$currentAction=="detalle") && $currentController!="purchaseOrders"){
      switch($currentController){
        case "invoices":
          $activeMenu="salesregistration";
          $activeSub="invoices";
          $sub="sub-menu-sales-registration";
          break;
        case "employees": 
					$activeMenu="configuration";
					$activeSub="employees";
					$sub="sub-menu-configuration";
					break;
				case "enterprises": 
					$activeMenu="configuration";
					$activeSub="enterprises";
					$sub="sub-menu-configuration";
					break;
        case "hoses": 
					$activeMenu="configuration";
					$activeSub="hoses";
					$sub="sub-menu-configuration";
					break;
       case "islands": 
					$activeMenu="configuration";
					$activeSub="islands";
					$sub="sub-menu-configuration";
					break;
        case "tanks": 
					$activeMenu="configuration";
					$activeSub="tanks";
					$sub="sub-menu-configuration";
					break;
        case "operators": 
					$activeMenu="configuration";
					$activeSub="operators";
					$sub="sub-menu-configuration";
					break;
        case "shifts": 
					$activeMenu="configuration";
					$activeSub="shifts";
					$sub="sub-menu-configuration";
					break;
        case "units": 
					$activeMenu="configuration";
					$activeSub="units";
					$sub="sub-menu-configuration";
					break;
        case "cashReceipts": 
					$activeMenu="finance";
					$activeSub="cashreceipts";
					$sub="sub-menu-finance";
					break;  
        case "closingDates": 
					$activeMenu="closingdates";
					$activeSub="inventory";
          $sub="sub-menu-inventory";
					break;  
      }
    }
   
    else if (($currentAction=="resumen"||$currentAction=="crear"||$currentAction=="editar"||$currentAction=="ver") && $currentController=="purchaseOrders"){
    $activeMenu="purchases";
    $activeSub="purchaseorders";
    $sub="sub-menu-entries";
    }
    else if (($currentAction=="resumenEntradas"||$currentAction=="crearEntrada"||$currentAction=="editarEntrada"||$currentAction=="verEntrada") && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="entries";
			$sub="sub-menu-entries";
		}
    else if (($currentAction=="resumenEntradasSuministros"||$currentAction=="crearEntradaSuministros"||$currentAction=="editarEntradaSuministros"||$currentAction=="verEntradaSuministros") && $currentController=="orders"){
			$activeMenu="purchases";
			$activeSub="entriesConsumibles";
			$sub="sub-menu-entries";
		}
    else if (($currentAction=="verProveedoresPorPagar"||$currentAction=="verFacturasPorPagar") && $currentController=="purchaseOrders"){
			$activeMenu="purchases";
			$activeSub="proveedoresPorPagar";
			$sub="sub-menu-entries";
		}
    else if ($currentAction=="resumenComprasRealizadas"&&$currentController=="orders"){
			$activeMenu="comprasrealizadas";
			//$activeSub="comprasrealizadas";
			//$sub="sub-menu-production";
		}
    else if ($currentAction=="reporteIncidencias"&&$currentController=="incidences"){
			$activeMenu="production";
			$activeSub="reportincidences";
			$sub="sub-menu-production";
		}
		else if ($currentAction=="inventario"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="inventory";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="ajustesInventario"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="adjustmentsInventory";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="resumenAjustesInventario"&&$currentController=="stockMovements"){
			$activeMenu="inventory";
			$activeSub="adjustmentsInventory";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="registrarPrecios"&&$currentController=="productPriceLogs"){
			$activeMenu="inventory";
			$activeSub="registrarprecios";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="resumenReclasificaciones"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="reclassification";
			$sub="sub-menu-inventory";
		}
		else if ($currentAction=="transferirLote"&&$currentController=="stockMovements"){
			$activeMenu="inventory";
			$activeSub="transferirlote";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="detalleCostoProducto"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="detallecostoproducto";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="cuadrarEstadosDeLote"&&$currentController=="stockItems"){
			$activeMenu="inventory";
			$activeSub="cuadrarestadodelotes";
			$sub="sub-menu-inventory";
		}
    else if ($currentAction=="reporteVentas" && $currentController=="orders"){
          $activeMenu="salesreports";
					$activeSub="reporteventas";
					$sub="sub-menu-sales-reports";
    }
    else if (($currentAction=="crearVenta"||$currentAction=="editarVenta"||$currentAction=="verVenta"||$currentAction=="registrarVentas") && $currentController=="orders"){
          $activeMenu="salesregistration";
					$activeSub="registrarventas";
					$sub="sub-menu-sales-registration";
    }
    else if ($currentAction=="reporteMedidasTanques" && $currentController=="tankMeasurements"){
			$activeMenu="salesreports";
			$activeSub="reportemedidastanques";
			$sub="sub-menu-sales-reports";
		}
    else if ($currentAction=="registrarMedidas" && $currentController=="tankMeasurements"){
			$activeMenu="salesregistration";
			$activeSub="tankmeasurements";
			$sub="sub-menu-sales-registration";
		}
    else if ($currentAction=="reporteMedidasMangueras" && $currentController=="hoseMeasurements"){
			$activeMenu="salesreports";
			$activeSub="reportemedidasmangueras";
			$sub="sub-menu-sales-reports";
		}
    else if ($currentAction=="registrarMedidas" && $currentController=="hoseMeasurements"){
			$activeMenu="salesregistration";
			$activeSub="hosemeasurements";
			$sub="sub-menu-sales-registration";
		}
    else if ($currentAction=="reporteRecibos" && $currentController=="paymentReceipts"){
      $activeMenu="salesreports";
      $activeSub="reporterecibos";
      $sub="sub-menu-sales-reports";
    }
    else if ($currentAction=="registrarRecibos" && $currentController=="paymentReceipts"){
			$activeMenu="salesregistration";
			$activeSub="paymentreceipts";
			$sub="sub-menu-sales-registration";
		}
    else if ($currentAction=="reporteEstimacionesCompras" && $currentController=="movementEstimates"){
			$activeMenu="salesregistration";
			$activeSub="reporteestimacionescompras";
			$sub="sub-menu-sales-registration";
		}
    else if ($currentAction=="informeDiario" && $currentController=="stockMovements"){
			$activeMenu="sales";
			$activeSub="informediario";
			$sub="sub-menu-sales-reports";
		}
    else if ($currentAction=="resumenDescuadresSubtotalesSumaProductosVentasRemisiones"&&$currentController=="orders"){
			$activeMenu="salesregistration";
			$activeSub="descuadresubtotalessumaproductos";
			$sub="sub-menu-sales-registration";
		}
    else if ($currentAction=="resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones"&&$currentController=="orders"){
			$activeMenu="salesregistration";
			$activeSub="descuadreredondeototales";
			$sub="sub-menu-sales-registration";
		}
		else if ($currentAction=="estadoResultados"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteestadoresultados";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProductos"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProducto"&&$currentController=="products"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteCompraVenta"&& $currentController=="stockMovements"){
			$activeMenu="reports";
			$activeSub="reporteproductos";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProduccionDetalle"&&$currentController=="stockItems"){
			$activeMenu="reports";
			$activeSub="reporteproducciondetalle";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="viewSaleReport"&&$currentController=="products"){
			$activeMenu="reports";
			$activeSub="reportesalidas";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteCierre"&&$currentController=="orders"){
			$activeMenu="reports";
			$activeSub="reportecierre";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteProduccionMeses"&&$currentController=="productionMovements"){
			$activeMenu="reports";
			$activeSub="reporteproduccionmeses";
			$sub="sub-menu-reports";
		}
		else if ($currentAction=="verReporteVentaProductoPorCliente"&&$currentController=="stockMovements"){
			$activeMenu="reports";
			$activeSub="reporteventaproductoporcliente";
			$sub="sub-menu-reports";
		}
    else if ($currentAction=="verReporteCaja" && $currentController=="accountingCodes"){
			$activeMenu="finance";
			//$activeSub="financereports";
			//$activeSecond="reportingresoscaja";
      $activeSub="reportingresoscaja";
			$sub="sub-menu-finance";
		}
    else if (($currentAction=="resumenDepositos"||$currentAction=="crearDeposito"||$currentAction=="editarDeposito"||$currentAction=="verDeposito") && $currentController=="transfers"){
			$activeMenu="finance";
			$activeSub="deposits";
			$sub="sub-menu-finance";
		}
    else if (($currentAction=="estadoCuentas" || $currentAction=="estadoCuentasCliente") && $currentController=="invoices"){
			$activeMenu="finance";
			$activeSub="estadocuentas";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verCobrosSemana" && $currentController=="invoices"){
			$activeMenu="finance";
			//$activeSub="financereports";
			//$activeSecond="cobrossemana";
      $activeSub="cobrossemana";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verClientesPorCobrar" && $currentController=="invoices"){
			$activeMenu="finance";
			//$activeSub="financereports";
			//$activeSecond="reportclientesporcobrar";
      $activeSub="reportclientesporcobrar";
			$sub="sub-menu-finance";
		}
    else if ($currentAction=="verHistorialPagos" && $currentController=="invoices"){
			$activeMenu="finance";
			//$activeSub="financereports";
			//$activeSecond="reporthistorialpagos";
      $activeSub="reporthistorialpagos";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verFacturasPorCobrar" && $currentController=="invoices"){
			$activeMenu="finance";
			//$activeSub="financereports";
			//$activeSecond="cuentasporpagar";
      $activeSub="cuentasporpagar";
			$sub="sub-menu-finance";
		}
    else if ($currentAction=="verCuentasPorPagar" && $currentController=="invoices"){
			$activeMenu="cuentasporpagar";
			//$activeSub="cuentasporpagar";
			//$activeSecond="cuentasporpagar";
			//$sub="sub-menu-finance";
		}
		else if ($currentAction=="verEstadoResultados" && $currentController=="accountingRegisters"){
			$activeMenu="finance";
			$activeSub="accounting";
			$activeSecond="reportestadoresultados";
			$sub="sub-menu-finance";
		}
		else if ($currentAction=="verBalanceGeneral" && $currentController=="accountingRegisters"){
			$activeMenu="finance";
			$activeSub="accounting";
			$activeSecond="reportbalancegeneral";
			$sub="sub-menu-finance";
		}
		else if (($currentAction=="resumenProveedores"||$currentAction=="crearProveedor"||$currentAction=="editarProveedor"||$currentAction=="verProveedor") && $currentController=="thirdParties"){
			$activeMenu="configuration";
			$activeSub="providers";
			//$activeSecond="reportbalancegeneral";
			$sub="sub-menu-configuration";
		}
		else if (($currentAction=="resumenClientes"||$currentAction=="crearCliente"||$currentAction=="editarCliente"||$currentAction=="verCliente" || $currentAction=="asociarClientesEmpresas") && $currentController=="thirdParties"){
			$activeMenu="configuration";
			$activeSub="clients";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction=="asociarClientesUsuarios" && $currentController=="thirdParties"){		
			$activeMenu="configuration";
			//$activeSub="asociarclientesusuarios";
      $activeSub="clients";
			$sub="sub-menu-configuration";
		}
		else if ($currentAction=="reasignarClientes" && $currentController=="thirdParties"){		
			$activeMenu="configuration";
			//$activeSub="reasignarclientes";
      $activeSub="clients";
			$sub="sub-menu-configuration";
		}
    else if ($currentAction=="registrarContadores" && $currentController=="hoseCounters"){
			$activeMenu="configuration";
			$activeSub="hosecounters";
			$sub="sub-menu-configuration";
		}
	
		
		$active=[];
		$active['activeMenu']=$activeMenu;
		$active['activeSub']=$activeSub;
		$active['activeSecond']=$activeSecond;
		//pr($sub);
		//pr($active);
    // For default settings name must be menu
    $this->set(compact('menu','active','sub'));
		
		$modificationInfo=NA;
		
		if($currentAction=="edit"||$currentAction=="view"
        ||$currentAction=="editarCliente"||$currentAction=="verCliente"
        ||$currentAction=="editarProveedor"||$currentAction=="verProveedor"
        ||$currentAction=="editarEntrada"||$currentAction=="verEntrada"
        ||$currentAction=="editarVenta"||$currentAction=="verVenta"
        ||$currentAction=="editarRemision"||$currentAction=="verRemision"){
			$this->loadModel('UserAction');
			$userActions=$this->UserAction->find('all',[
				'fields'=>[
					'UserAction.action_name','UserAction.action_datetime',
					'UserAction.user_id','User.username',
				],
				'conditions'=>[
					'UserAction.controller_name'=>$currentController,
					'UserAction.item_id'=>$currentParameter,
				],
				'order'=>'action_datetime DESC',
			]);
			//pr($userActions);
			if (!empty($userActions)){
				
				$lastAction="";
        $actionName=$userActions[0]['UserAction']['action_name'];
				if ($actionName==="add"
          || $actionName==="crear"
          || $actionName==="crearCliente"
          || $actionName==="crearProveedor"
          || $actionName==="crearEntrada"
          || $actionName==="crearVenta"
          || $actionName==="crearRemision" 
          || $actionName==="crearDeposito" 
          ){
					$lastAction="Grabado por ";
				}
				if ($actionName==="edit"
          || $actionName==="editar"
          || $actionName==="editarCliente"
          || $actionName==="editarProveedor"
          || $actionName==="editarEntrada"
          || $actionName==="editarVenta"
          || $actionName==="editarRemision" 
          || $actionName==="editarDeposito" 
          ){
          $lastAction="Modificado por ";
				}
				
				$lastAction.=$userActions[0]['User']['username']." ";
				
				$actionDateTime=new DateTime($userActions[0]['UserAction']['action_datetime']);
				$lastAction.=$actionDateTime->format('d-m-Y H:i:s');
				$modificationInfo="";
				//$modificationInfo="<ul class='nav pull-right' style='position:absolute;right:300px;top:30px;'>";
				//$modificationInfo.="<div class='btn-group'>";
				//	$modificationInfo.="<a class='btn dropdown-toggle' data-toggle='dropdown' href='#'> Action<span class='caret'></span></a>";
				
				//$modificationInfo.="<ul class='nav pull-right'>";
				$modificationInfo.="<ul class='nav'>";
					$modificationInfo.="<li class='dropdown'>";
						$modificationInfo.="<a class='dropdown-toggle' data-toggle='dropdown' href='#'>";
							$modificationInfo.=$lastAction;
							$modificationInfo.="<i class='icon-angle-down'></i>";
						$modificationInfo.="</a>";
						
						if (count($userActions)>1){
							
							$modificationInfo.="<ul class='dropdown-menu'>";
							for ($i=1;$i<count($userActions);$i++){
								$actionInfo="";
								if ($userActions[$i]['UserAction']['action_name']=="add"){
									$actionInfo="Grabado por ";
								}
								elseif ($userActions[$i]['UserAction']['action_name']=="edit"){
									$actionInfo="Modificado por ";
								}
								$actionInfo.=$userActions[$i]['User']['username']." ";
								$actionDateTime=new DateTime($userActions[$i]['UserAction']['action_datetime']);
								$actionInfo.=$actionDateTime->format('d-m-Y H:i:s');
							
							
								$modificationInfo.="<li>";
									$modificationInfo.="<i class='icon-key'></i>";
									$modificationInfo.=$actionInfo;
								$modificationInfo.="</li>";
							}	
							$modificationInfo.="</ul>";
						}
					$modificationInfo.="</li>";
				$modificationInfo.="</ul>";			
				//$modificationInfo.="</div>";
			}
		}
		
		$this->set(compact('modificationInfo'));
		
		if (!(($currentController=='pages')&&($currentAction=='display'||$currentAction=='productionconfig'))){
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/add";		
			//pr($aco_name);
			$userid=$this->Auth->User('id');
			//pr($userid);
			if (!empty($userid)){
				$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_add_permission=false;
			}
			//echo "bool add permission is ".$bool_add_permission."<br/>";
			$this->set(compact('bool_add_permission'));
			
			
			$userid=$this->Auth->User('id');
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/edit";		
			//pr($userid);
			if (!empty($userid)){
				$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_edit_permission=false;
			}
			//echo "bool edit permission is ".$bool_edit_permission."<br/>";
			$this->set(compact('bool_edit_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/delete";		
			if (!empty($userid)){
				$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_delete_permission=false;
			}
			//echo "bool delete permission is ".$bool_delete_permission."<br/>";
			$this->set(compact('bool_delete_permission'));
			
			$aco_name=Inflector::camelize(Inflector::pluralize($currentController))."/annul";		
			if (!empty($userid)){
				$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
			}
			else {
				$bool_annul_permission=false;
			}
			//echo "bool annul permission is ".$bool_annul_permission."<br/>";
			$this->set(compact('bool_annul_permission'));
		}
		
		$exchangeRateUpdateNeeded=false;
		$this->loadModel('ExchangeRate');
		$exchangeRateDuration=$this->ExchangeRate->getLatestExchangeRateDuration();
		//echo "exchange rate duration is ".$exchangeRateDuration."<br/>";
		if ($exchangeRateDuration>31){
			$exchangeRateUpdateNeeded=true;
		}
		$this->set(compact('exchangeRateUpdateNeeded'));
	//	if($exchangeRateUpdateNeeded){
	//		echo "<script>alert('Se venció la tasa de cambio, por favor introduzca la nueva tasa de cambio!');</script>";
	//	}
  
    //pr($user);
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('ProductPriceLog');
    
    $this->loadModel('TankMeasurement');
    
    $priceUpdateNeeded=[];
    $inventoryMeasurementCorrectionNeeded=[];
      
    if ($loggedUserId > 0){
      $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
      //pr($enterprises);
      
      if (count($enterprises) == 1){
        $enterpriseId=array_keys($enterprises)[0];
      }
      //pr($enterprises);
      foreach ($enterprises as $enterpriseId=>$enterpriseName){
        $priceUpdateNeeded[$enterpriseId]=false;
        
        $latestFuelProductPriceLog=$this->ProductPriceLog->getLatestFuelProductPriceLog($enterpriseId);
        //pr($latestFuelProductPriceLog);
        $fuelPriceExpiration=$latestFuelProductPriceLog['ProductPriceLog']['duration'];
        //pr($fuelPriceExpiration);
        if ($fuelPriceExpiration>6){
          $priceUpdateNeeded[$enterpriseId]=true;
        }
        
        $inventoryMeasurementCorrectionNeeded[$enterpriseId]=false;
        $inventoryMeasurementStatus=$this->TankMeasurement->getCurrentInventoryTankMeasurementStatus($enterpriseId);
        //pr($inventoryMeasurementStatus);
        if ($inventoryMeasurementStatus['measurements_present'] && !$inventoryMeasurementStatus['adjustments_present'] && $inventoryMeasurementStatus['week_day']>0){
          $inventoryMeasurementCorrectionNeeded[$enterpriseId]=true;
        }
      }
      
      //echo "controller is ".$currentController."<br/>";
      //echo "action is ".$currentAction."<br/>";
      //echo "parameter is ".$currentParameter."<br/>";
      
      if (count($enterprises) == 0 
        && !($currentController=='pages' && $currentAction == 'display' && $currentParameter == 'alertaAsociacionAusente') 
        && !($currentController == 'users' && $currentAction == 'logout')){
        return $this->redirect(['controller'=>'pages','action' => 'display','alertaAsociacionAusente']);
      }
    }
    //pr($enterprises);
    $this->set(compact('enterprises'));
    $this->set(compact('enterpriseId'));
      
    $this->set(compact('priceUpdateNeeded','inventoryMeasurementCorrectionNeeded'));  
    
  }
	
	public function hasPermission($user_id,$aco_name){
		$this->loadModel('User');
		$user=$this->User->find('first',[
      'conditions'=>['User.id'=>$user_id]
    ]);
		//pr($user);
		//pr($aco_name);
		if (!empty($user)){
			return $this->Acl->check(array('Role'=>array('id'=>$user['User']['role_id'])),$aco_name);
		}
		else {
			return false;
		}
	}

	public function userhome($userRoleId){
    //pr($userRoleId);
		switch ($userRoleId){
			case ROLE_ADMIN:
        return [
					'controller' => 'orders',
          'action' => 'reporteVentas',
				];
				break;
      case ROLE_ASSISTANT:
        return [
					'controller' => 'orders',
          'action' => 'registrarVentas',
				];
				break;
			case ROLE_MANAGER:
      case ROLE_ACCOUNTING:
				return [
					'controller' => 'orders',
					'action' => 'registrarVentas',
				];
				break;
      case ROLE_SALES:
				return [
					'controller' => 'stock_items',
					'action' => 'inventario'
				];
        break;
      case ROLE_CLIENT:
				return [
					'controller' => 'invoices',
					'action' => 'verCuentasPorPagar'
				];
        break;  
			default:
        return [
				  'controller' => 'users',
				  'action' => 'login'
				];
		}
	}
	
	public static function normalizeChars($s) {
		$replace = array(
			'ъ'=>'-', 'Ь'=>'-', 'Ъ'=>'-', 'ь'=>'-',
			'Ă'=>'A', 'Ą'=>'A', 'À'=>'A', 'Ã'=>'A', 'Á'=>'A', 'Æ'=>'A', 'Â'=>'A', 'Å'=>'A', 'Ä'=>'Ae',
			'Þ'=>'B',
			'Ć'=>'C', 'ץ'=>'C', 'Ç'=>'C',
			'È'=>'E', 'Ę'=>'E', 'É'=>'E', 'Ë'=>'E', 'Ê'=>'E',
			'Ğ'=>'G',
			'İ'=>'I', 'Ï'=>'I', 'Î'=>'I', 'Í'=>'I', 'Ì'=>'I',
			'Ł'=>'L',
			'Ñ'=>'N', 'Ń'=>'N',
			'Ø'=>'O', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe',
			'Ş'=>'S', 'Ś'=>'S', 'Ș'=>'S', 'Š'=>'S',
			'Ț'=>'T',
			'Ù'=>'U', 'Û'=>'U', 'Ú'=>'U', 'Ü'=>'Ue',
			'Ý'=>'Y',
			'Ź'=>'Z', 'Ž'=>'Z', 'Ż'=>'Z',
			'â'=>'a', 'ǎ'=>'a', 'ą'=>'a', 'á'=>'a', 'ă'=>'a', 'ã'=>'a', 'Ǎ'=>'a', 'а'=>'a', 'А'=>'a', 'å'=>'a', 'à'=>'a', 'א'=>'a', 'Ǻ'=>'a', 'Ā'=>'a', 'ǻ'=>'a', 'ā'=>'a', 'ä'=>'ae', 'æ'=>'ae', 'Ǽ'=>'ae', 'ǽ'=>'ae',
			'б'=>'b', 'ב'=>'b', 'Б'=>'b', 'þ'=>'b',
			'ĉ'=>'c', 'Ĉ'=>'c', 'Ċ'=>'c', 'ć'=>'c', 'ç'=>'c', 'ц'=>'c', 'צ'=>'c', 'ċ'=>'c', 'Ц'=>'c', 'Č'=>'c', 'č'=>'c', 'Ч'=>'ch', 'ч'=>'ch',
			'ד'=>'d', 'ď'=>'d', 'Đ'=>'d', 'Ď'=>'d', 'đ'=>'d', 'д'=>'d', 'Д'=>'D', 'ð'=>'d',
			'є'=>'e', 'ע'=>'e', 'е'=>'e', 'Е'=>'e', 'Ə'=>'e', 'ę'=>'e', 'ĕ'=>'e', 'ē'=>'e', 'Ē'=>'e', 'Ė'=>'e', 'ė'=>'e', 'ě'=>'e', 'Ě'=>'e', 'Є'=>'e', 'Ĕ'=>'e', 'ê'=>'e', 'ə'=>'e', 'è'=>'e', 'ë'=>'e', 'é'=>'e',
			'ф'=>'f', 'ƒ'=>'f', 'Ф'=>'f',
			'ġ'=>'g', 'Ģ'=>'g', 'Ġ'=>'g', 'Ĝ'=>'g', 'Г'=>'g', 'г'=>'g', 'ĝ'=>'g', 'ğ'=>'g', 'ג'=>'g', 'Ґ'=>'g', 'ґ'=>'g', 'ģ'=>'g',
			'ח'=>'h', 'ħ'=>'h', 'Х'=>'h', 'Ħ'=>'h', 'Ĥ'=>'h', 'ĥ'=>'h', 'х'=>'h', 'ה'=>'h',
			'î'=>'i', 'ï'=>'i', 'í'=>'i', 'ì'=>'i', 'į'=>'i', 'ĭ'=>'i', 'ı'=>'i', 'Ĭ'=>'i', 'И'=>'i', 'ĩ'=>'i', 'ǐ'=>'i', 'Ĩ'=>'i', 'Ǐ'=>'i', 'и'=>'i', 'Į'=>'i', 'י'=>'i', 'Ї'=>'i', 'Ī'=>'i', 'І'=>'i', 'ї'=>'i', 'і'=>'i', 'ī'=>'i', 'ĳ'=>'ij', 'Ĳ'=>'ij',
			'й'=>'j', 'Й'=>'j', 'Ĵ'=>'j', 'ĵ'=>'j', 'я'=>'ja', 'Я'=>'ja', 'Э'=>'je', 'э'=>'je', 'ё'=>'jo', 'Ё'=>'jo', 'ю'=>'ju', 'Ю'=>'ju',
			'ĸ'=>'k', 'כ'=>'k', 'Ķ'=>'k', 'К'=>'k', 'к'=>'k', 'ķ'=>'k', 'ך'=>'k',
			'Ŀ'=>'l', 'ŀ'=>'l', 'Л'=>'l', 'ł'=>'l', 'ļ'=>'l', 'ĺ'=>'l', 'Ĺ'=>'l', 'Ļ'=>'l', 'л'=>'l', 'Ľ'=>'l', 'ľ'=>'l', 'ל'=>'l',
			'מ'=>'m', 'М'=>'m', 'ם'=>'m', 'м'=>'m',
			'ñ'=>'n', 'н'=>'n', 'Ņ'=>'n', 'ן'=>'n', 'ŋ'=>'n', 'נ'=>'n', 'Н'=>'n', 'ń'=>'n', 'Ŋ'=>'n', 'ņ'=>'n', 'ŉ'=>'n', 'Ň'=>'n', 'ň'=>'n',
			'о'=>'o', 'О'=>'o', 'ő'=>'o', 'õ'=>'o', 'ô'=>'o', 'Ő'=>'o', 'ŏ'=>'o', 'Ŏ'=>'o', 'Ō'=>'o', 'ō'=>'o', 'ø'=>'o', 'ǿ'=>'o', 'ǒ'=>'o', 'ò'=>'o', 'Ǿ'=>'o', 'Ǒ'=>'o', 'ơ'=>'o', 'ó'=>'o', 'Ơ'=>'o', 'œ'=>'oe', 'Œ'=>'oe', 'ö'=>'oe',
			'פ'=>'p', 'ף'=>'p', 'п'=>'p', 'П'=>'p',
			'ק'=>'q',
			'ŕ'=>'r', 'ř'=>'r', 'Ř'=>'r', 'ŗ'=>'r', 'Ŗ'=>'r', 'ר'=>'r', 'Ŕ'=>'r', 'Р'=>'r', 'р'=>'r',
			'ș'=>'s', 'с'=>'s', 'Ŝ'=>'s', 'š'=>'s', 'ś'=>'s', 'ס'=>'s', 'ş'=>'s', 'С'=>'s', 'ŝ'=>'s', 'Щ'=>'sch', 'щ'=>'sch', 'ш'=>'sh', 'Ш'=>'sh', 'ß'=>'ss',
			'т'=>'t', 'ט'=>'t', 'ŧ'=>'t', 'ת'=>'t', 'ť'=>'t', 'ţ'=>'t', 'Ţ'=>'t', 'Т'=>'t', 'ț'=>'t', 'Ŧ'=>'t', 'Ť'=>'t', '™'=>'tm',
			'ū'=>'u', 'у'=>'u', 'Ũ'=>'u', 'ũ'=>'u', 'Ư'=>'u', 'ư'=>'u', 'Ū'=>'u', 'Ǔ'=>'u', 'ų'=>'u', 'Ų'=>'u', 'ŭ'=>'u', 'Ŭ'=>'u', 'Ů'=>'u', 'ů'=>'u', 'ű'=>'u', 'Ű'=>'u', 'Ǖ'=>'u', 'ǔ'=>'u', 'Ǜ'=>'u', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'У'=>'u', 'ǚ'=>'u', 'ǜ'=>'u', 'Ǚ'=>'u', 'Ǘ'=>'u', 'ǖ'=>'u', 'ǘ'=>'u', 'ü'=>'ue',
			'в'=>'v', 'ו'=>'v', 'В'=>'v',
			'ש'=>'w', 'ŵ'=>'w', 'Ŵ'=>'w',
			'ы'=>'y', 'ŷ'=>'y', 'ý'=>'y', 'ÿ'=>'y', 'Ÿ'=>'y', 'Ŷ'=>'y',
			'Ы'=>'y', 'ž'=>'z', 'З'=>'z', 'з'=>'z', 'ź'=>'z', 'ז'=>'z', 'ż'=>'z', 'ſ'=>'z', 'Ж'=>'zh', 'ж'=>'zh'
		);
		return strtr($s, $replace);
	}	
	
	public function recreateStockItemLogs($id,$stockItemDate) {
    $this->loadModel('StockItemLog');  
		$this->StockItem->id = $id;
		if (!$this->StockItem->exists()) {
			throw new NotFoundException(__('Invalid stock item'));
		}
		//$this->request->allowMethod('post', 'delete');
		$stockItem=$this->StockItem->find('first',[
			'conditions'=>['StockItem.id'=>$id],
			'contain'=>[
        'StockItemLog'=>[
          'conditions'=>['StockItemLog.stock_item_date >='=>$stockItemDate],
        ],
			]
		]);
		//pr($stockItem);
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try{
      if (!empty($stockItem['StockItemLog'])){
        //pr($stockItem['StockItemLog']);
        foreach ($stockItem['StockItemLog'] as $stockItemLog){
          //pr($stockItemLog);
          if (!$this->StockItemLog->delete($stockItemLog['id'])) {
            echo "problema eliminando los estados de lote";
            pr($this->validateErrors($this->StockItemLog));
            throw new Exception();
          }
        }
      }
			$datasource->commit();
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			return false;
		}
    // now recreate the stock item logs
    $stockItem=$this->StockItem->find('first',[
			'conditions'=>['StockItem.id'=>$id],
			'contain'=>[
        'StockMovement'=>[
          'conditions'=>['StockMovement.movement_date >='=>$stockItemDate],
          'order'=>'StockMovement.movement_date ASC, StockMovement.id ASC',
        ],
				'StockItemLog'=>[
          // posterior stock item logs already deleted
          'order'=>'StockItemLog.stock_item_date DESC, StockItemLog.id DESC'
        ],
			]
		]);
    //pr($stockItem);
    $stockItemLogQuantity=0;
    $stockItemLogUnitCost=0;
		if (!empty($stockItem['StockItemLog'])){
      $stockItemLogQuantity=$stockItem['StockItemLog'][0]['product_quantity'];
      $stockItemLogUnitCost=$stockItem['StockItemLog'][0]['product_unit_cost'];
    }
		$datasource=$this->StockItem->getDataSource();
		$datasource->begin();
		try {
      foreach ($stockItem['StockMovement'] as $movement){
        if ($movement['bool_input']){
          $newStockItemLogQuantity=$stockItemLogQuantity + $movement['product_quantity'];
          $newStockItemLogUnitCost=($stockItemLogQuantity*$stockItemLogUnitCost + $movement['product_total_price'])/$newStockItemLogQuantity;    
        }
        else {
          $newStockItemLogQuantity=$stockItemLogQuantity - $movement['product_quantity'];
          $newStockItemLogUnitCost=$stockItemLogUnitCost;  
        }
       
        $stockItemLogData=[];
        $stockItemLogData['stock_item_id']=$id;
        $stockItemLogData['stock_movement_id']=$movement['id'];
        $stockItemLogData['stock_item_date']=$movement['movement_date'];
        $stockItemLogData['product_id']=$movement['product_id'];
        $stockItemLogData['product_quantity']=$newStockItemLogQuantity;
        $stockItemLogData['product_unit_cost']=$newStockItemLogUnitCost;
					
        $this->StockItemLog->create();
        if (!$this->StockItemLog->save($stockItemLogData)) {
          echo "problema guardando los estado de lote";
          pr($this->validateErrors($this->StockItemLog));
          throw new Exception();
        }
        
        $stockItemLogQuantity=$newStockItemLogQuantity;
        $stockItemLogUnitCost=$newStockItemLogUnitCost;
			}
			$datasource->commit();
			return true;
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			return false;
		}
	}

	public function get_date($month, $year, $week, $day, $direction) {
		if($direction > 0)
			$startday = 1;
		else
			// t gives number of days in given month, from 28 to 31
			// mktime(hour, minute, second, month, day, year, daylightsavingtime)
			$startday = date('t', mktime(0, 0, 0, $month, 1, $year));

		$start = mktime(0, 0, 0, $month, $startday, $year);
		// N gives numberic representation of weekday 1 (for Monday) through 7 (for Sunday)
		$weekday = date('N', $start);

		if($direction * $day >= $direction * $weekday)
			$offset = -$direction * 7;
		else
			$offset = 0;

		$offset += $direction * ($week * 7) + ($day - $weekday);
		return mktime(0, 0, 0, $month, $startday + $offset, $year);
	}
	
	public function saveAccountingRegisterData($AccountingRegisterDataArray,$bool_new){
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingCode');
		$this->loadModel('Currency');
		$datasource=$this->AccountingRegister->getDataSource();
		$datasource->begin();
		try {
			//pr($AccountingRegisterDataArray);
			if ($bool_new){
				$this->AccountingRegister->create();
			}
			if (!$this->AccountingRegister->save($AccountingRegisterDataArray)) {
				pr($this->validateErrors($this->AccountingRegister));
				echo "Error al guardar el asiento contable";
				throw new Exception();
			}
			
			$accounting_register_id=$this->AccountingRegister->id;
			$accounting_register_accounting_register_type_id=$AccountingRegisterDataArray['AccountingRegister']['accounting_register_type_id'];
			$accounting_register_register_code=$AccountingRegisterDataArray['AccountingRegister']['register_code'];
			$accounting_register_concept=$AccountingRegisterDataArray['AccountingRegister']['concept'];
			$accounting_register_date=$AccountingRegisterDataArray['AccountingRegister']['register_date'];
			$accounting_register_currency_id=$AccountingRegisterDataArray['AccountingRegister']['currency_id'];
			//$linkedCurrency=$this->Currency->read(null,$accounting_register_currency_id);
			//$currency_abbreviation=$linkedCurrency['Currency']['abbreviation'];
			$currency_abbreviation="C$";
			foreach ($AccountingRegisterDataArray['AccountingMovement'] as $accountingMovement){
				//pr($accountingMovement);
				$accounting_movement_amount=0;
				$bool_debit=true;
				
				if (!empty($accountingMovement['debit_amount'])){
					$accounting_movement_amount = round($accountingMovement['debit_amount'],2);
					$bool_debit=true;
				}
				else if (!empty($accountingMovement['credit_amount'])){
					$accounting_movement_amount = round($accountingMovement['credit_amount'],2);
					$bool_debit=false;
				}
				
				$accounting_movement_code_id = $accountingMovement['accounting_code_id'];
				$accounting_movement_concept = $accountingMovement['concept'];
				
				//echo "just before the saving part of accountingmovements.<br/>";
				//echo "accounting movement code id".$accounting_movement_code_id."<br/>";
				//echo "accounting movement amount".$accounting_movement_amount."<br/>";
				if ($accounting_movement_code_id>0 && $accounting_movement_amount>0){
					$accountingCode=$this->AccountingCode->read(null,$accounting_movement_code_id);
					$accounting_movement_code_description = $accountingCode['AccountingCode']['description'];
					
					$logmessage="Registro de cuenta contable ".$accounting_movement_code_description." (Monto:".$accounting_movement_amount." ".$currency_abbreviation.") para Registro Contable ".$accounting_register_concept;
					//echo $logmessage."<br/>";
					// SAVE ACCOUNTING MOVEMENT
					$AccountingMovementItemData['accounting_register_id']=$accounting_register_id;
					$AccountingMovementItemData['accounting_code_id']=$accounting_movement_code_id;
					$AccountingMovementItemData['concept']=$accounting_movement_concept;
					
					
					$AccountingMovementItemData['amount']=$accounting_movement_amount;
					$AccountingMovementItemData['currency_id']=$accounting_register_currency_id;
					
					$AccountingMovementItemData['bool_debit']=$bool_debit;
					//echo "saved item data";
					//pr($AccountingMovementItemData);
					$this->AccountingRegister->AccountingMovement->create();
					if (!$this->AccountingRegister->AccountingMovement->save($AccountingMovementItemData)) {
						pr($this->validateErrors($this->AccountingMovement));
						echo "problema al guardar el movimiento contable";
						throw new Exception();
					}
					
					// SAVE THE USERLOG FOR ACCOUNTING MOVEMENT
					$this->recordUserActivity($this->Session->read('User.username'),$logmessage);
				}
			}			
			$datasource->commit();
			$this->Session->setFlash(__('Se guardó el comprobante.'),'default',array('class' => 'success'));
			return $accounting_register_id;
			
		}
		catch(Exception $e){
			$datasource->rollback();
			$this->Session->setFlash(__('No se podía guardar el comprobante. Por favor intente de nuevo.'),'default',array('class' => 'error-message'));
			return false;
		}
	}
	
}