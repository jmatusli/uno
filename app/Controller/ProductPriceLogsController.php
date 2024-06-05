<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductPriceLogsController extends AppController {
	
  public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');
	
  public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('');
	}
  
  public function registrarPrecios(){
    $this->loadModel('ClosingDate');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
		
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
		
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		//$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $enterpriseId=0;
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
      $priceDateTimeArray=$this->request->data['ProductPriceLog']['price_datetime'];
      $priceDateTimeAsString=$this->ProductPriceLog->deconstruct('price_datetime',$this->request->data['ProductPriceLog']['price_datetime']);
      //$priceDateTimeAsString=$priceDateTimeArray['year'].'-'.$priceDateTimeArray['month'].'-'.$priceDateTimeArray['day'];
      $priceDateTime=date( "Y-m-d H:i:s", strtotime($priceDateTimeAsString));
      //$priceDateTime=date( "Y-m-d", strtotime($priceDateTimeAsString));
      
      $enterpriseId=$this->request->data['ProductPriceLog']['enterprise_id'];
		}
    //elseif (!empty($_SESSION['priceDateTime'])){
    //  //echo 0"datetime from session.<br/>";
		//	$priceDateTime=$_SESSION['priceDateTime'];
		//}
		else {
      //echo "datetime from date function.<br/>";
			$priceDateTime = date("Y-m-d H:i:s");
      //$priceDateTime = date("Y-m-d");
		}
		$_SESSION['priceDateTime']=$priceDateTime;
		$priceDateTimeAsString=$priceDateTime;
    $priceDateTimePlusOne= date( "Y-m-d", strtotime( $priceDateTime."+1 days" ) );
    //echo "datetime is ".$priceDateTime."<br/>";
    $this->set(compact('priceDateTime'));
    
    $priceDate=date( "Y-m-d", strtotime($priceDateTimeAsString));

    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    
    if ($this->request->is('post')) {	
      if (empty($this->request->data['changeDate'])){
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
        if ($priceDateTimeAsString > date( "Y-m-d", strtotime(date('Y-m-d')."+7 days" ))){
          //echo 'priceDateTimeAsString is '.$priceDateTimeAsString.'<br/>';
          $this->Session->setFlash(__('La fecha de los recibos no se puyeden programar más que una semana en el futuro!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
        }
        elseif ($priceDateTimeAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los precios.'), 'default',['class' => 'error-message']);
        }
        else {
          //pr($this->request->data);
          //get prices registered for current day
          $products=$this->Product->find('all',[
            'contain'=>[        
              'ProductPriceLog'=>[
                'conditions'=>[
                  'DATE(ProductPriceLog.price_datetime)'=>$priceDate,
                  'ProductPriceLog.enterprise_id'=>$enterpriseId,
                ],
                'order'=>'ProductPriceLog.price_datetime DESC',
                'limit'=>1,
              ],
            ],
            'order'=>'Product.name ASC',
          ]);
          $productPriceList=[];
          foreach ($products as $product){
            $productId=$product['Product']['id'];
            if (!empty($product['ProductPriceLog'])){
              $productPriceList[$productId]=$product['ProductPriceLog'][0]['price'];
            }
            else {
              $productPriceList[$productId]=-1;
            }
          }
          $datasource=$this->ProductPriceLog->getDataSource();
          $datasource->begin();
          
          try {
            $productlessArray=[
              'price_datetime'=>$priceDateTimeAsString,
              'currency_id'=>$this->request->data['ProductPriceLog']['currency_id'],
              'user_id'=>$this->request->data['ProductPriceLog']['user_id'],
              'enterprise_id'=>$this->request->data['ProductPriceLog']['enterprise_id'],
            ];
            foreach ($this->request->data['Product'] as $productId=>$productPriceArray){  
              if ($productPriceArray['price'] != $productPriceList[$productId] && $productPriceArray['price']>0){
                $productPriceData=$productlessArray;
                $productPriceData['product_id']=$productId;
                $productPriceData['price']=$productPriceArray['price'];
                //pr($productPriceData);
                
                $this->ProductPriceLog->create();
                if (!$this->ProductPriceLog->save($productPriceData)) {
                  echo "Problema guardando el precio del producto ".$productId;
                  pr($this->validateErrors($this->ProductPriceLog));
                  throw new Exception();
                }
              }            
            }                
            $datasource->commit();
            $this->recordUserAction();
            // SAVE THE USERLOG 
            $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los precios de venta para fecha ".$priceDateTimeAsString);
            $this->Session->setFlash("Se registraron los precios de venta para fecha ".$priceDateTimeAsString,'default',['class' => 'success'],'default',['class' => 'success']);
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash("No se podían registrar los precios de venta para fecha ".$priceDateTimeAsString, 'default',['class' => 'error-message']);
          }
        }	
      }
    }
    
    $productTypes=$this->ProductType->find('all',[
      'contain'=>[
        'Product'=>[
          'ProductPriceLog'=>[
            'conditions'=>[
              'DATE(ProductPriceLog.price_datetime)'=>$priceDate,
              'ProductPriceLog.enterprise_id'=>$enterpriseId,
            ],
            'order'=>'ProductPriceLog.price_datetime DESC',
            'limit'=>1,
          ],
          'PreviousProductPriceLog'=>[
            'conditions'=>[
              'PreviousProductPriceLog.price_datetime <'=>$priceDateTime,
              'PreviousProductPriceLog.enterprise_id'=>$enterpriseId,
            ],
            'order'=>'PreviousProductPriceLog.price_datetime DESC',
            'limit'=>1,
          ],
           'order'=>'Product.product_order ASC',
        ],
      ],
    ]);
    $this->set(compact('productTypes'));
    
    $this->loadModel('Currency');
    $currencies=$this->Currency->find('list');
    $this->set(compact('currencies'));
  }
  
}
