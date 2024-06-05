<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class HoseCountersController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		//$this->Auth->allow('');		
	}

  public function registrarContadores() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
    
    $this->loadModel('Hose');
    $this->loadModel('Island');
		
    $roleId = $this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		//$enterpriseId=ENTERPRISE_LAS_PALMAS;
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $shiftId=SHIFT_NIGHT;
    $counterDate = null;
    if (!empty($_SESSION['counterDate'])){
			$counterDate=$_SESSION['counterDate'];
		}
		else {
			$counterDate = date("Y-m-d");
		}
		$_SESSION['counterDate']=$counterDate;
    $counterDateString=$counterDate;
		$counterDatePlusOne=date("Y-m-d",strtotime($counterDate."+1 days"));
    
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
      $enterpriseId=$this->request->data['HoseCounter']['enterprise_id'];
    }
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $enterpriseIslandIds=array_keys($this->Island->getIslandListForEnterprise($enterpriseId));
    
    $requestHoseCounters=[];
    $boolEditingMode=false;
    $boolEditingToggleVisible=false;
    
    if ($this->request->is('post')) {
      //pr($this->request->data);
      $shiftId=$this->request->data['HoseCounter']['shift_id'];
      if (!empty($this->request->data['changeDate'])){  
        $counterDateArray=$this->request->data['HoseCounter']['counter_date'];
        $counterDateString=$counterDateArray['year'].'-'.$counterDateArray['month'].'-'.$counterDateArray['day'];
        $counterDate=date( "Y-m-d", strtotime($counterDateString));
        $_SESSION['counterDate']=$counterDate;
        
        $hoses=$this->Hose->find('all',[
          'conditions'=>['Hose.island_id'=>$enterpriseIslandIds,],
          'contain'=>[
            'HoseCounter'=>[
              'conditions'=>[
                'counter_date'=>$counterDate,
                'shift_id'=>$shiftId,
              ]
            ]
          ],
        ]);
        //pr($hoses);
        foreach ($hoses as $hose){
          if (!empty($hose['HoseCounter'])){
            $requestHoseCounters[$hose['Hose']['id']]=$hose['HoseCounter'][0]['counter_value'];
          }
        }
        //pr($requestHoseCounters);
        if (empty($requestHoseCounters)){
          $boolEditingMode=true;
        }
        else {
          $boolEditingToggleVisible=true;
        }
      }
      else {
        foreach ($this->request->data['Hose'] as $hoseId=>$hoseData){
          $requestHoseCounters[$hoseId]=$hoseData['HoseCounter']['counter_value'];
        }
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate($enterpriseId);
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
         
        $latestHoseCounterAfterSelectedDay=$this->HoseCounter->find('first',[
          'fields'=>['counter_date'],
          'conditions'=>[
            'counter_date >'=>$counterDate,
            'HoseCounter.enterprise_id'=>$enterpriseId,
          ],
          'limit'=>1,
          'order'=>'counter_date DESC',
        ]);
        //echo "counter date string is ".$counterDateString."<br/>";
        //pr($latestHoseCounterAfterSelectedDay);
        
        if ($counterDateString>date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de los contadores no puede estar en el futuro!  No se guardaron los contadores.'), 'default',['class' => 'error-message']);
        }
        elseif ($counterDateString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardaron los contadores.'), 'default',['class' => 'error-message']);
        }
        elseif (!empty($latestHoseCounterAfterSelectedDay)){
          $this->Session->setFlash("Ya existen contadores análogos de una fecha posterior a la fecha seleccionada.  Hay que remover estas contadores primeras, comenzando con los contadores de ".$latestHoseCounterAfterSelectedDay['HoseCounter']['counter_date'], 'default',['class' => 'error-message']);
        }
        else {
          $datasource=$this->HoseCounter->getDataSource();
          $datasource->begin();
          try {
            // FIRST REMOVE PREVIOUS VALUES OF HOSECOUNTER FOR EDITING
            $hoses=$this->Hose->find('all',[
              'conditions'=>['Hose.island_id'=>$enterpriseIslandIds,],
              'contain'=>[
                'HoseCounter'=>[
                  'conditions'=>[
                    'counter_date'=>$counterDate,
                    'shift_id'=>$shiftId,
                    'enterprise_id'=>$enterpriseId,
                  ]
                ]
              ],
            ]);
            //pr($hoses);
            foreach ($hoses as $hose){
              if (!empty($hose['HoseCounter'])){
                foreach ($hose['HoseCounter'] as $hoseCounter){
                  if (!$this->HoseCounter->delete($hoseCounter['id'])) {
                    echo "Problema eliminando la medida electrónica de manguera obsoleta";
                    pr($this->validateErrors($this->HoseCounter));
                    throw new Exception();
                  }
                }
              }
            }
            // THEN SAVE THE NEW DATA
            foreach ($this->request->data['Hose'] as $hoseId=>$hoseData){
              $hoseCounterData=$hoseData['HoseCounter'];
              $hoseCounterData['hose_id']=$hoseId;
              $hoseCounterData['counter_date']=$counterDateString;
              $hoseCounterData['shift_id']=$shiftId;
              $hoseCounterData['enterprise_id']=$enterpriseId;
              //pr($hoseCounterData);
              
              $this->HoseCounter->create();
              if (!$this->HoseCounter->save($hoseCounterData)) {
                echo "Problema guardando la medida electrónica de manguera";
                pr($this->validateErrors($this->HoseCounter));
                throw new Exception();
              }
            }
           
            $datasource->commit();
            $this->recordUserAction();
            // SAVE THE USERLOG 
            $this->recordUserActivity($this->Session->read('User.username'),"Se registraron los contadores análogos de fecha ".$counterDateString);
            $this->Session->setFlash("Se registraron los contadores análogos de fecha ".$counterDateString,'default',['class' => 'success'],'default',['class' => 'success']);
            $boolEditingMode=false;
            $boolEditingToggleVisible=true;
            
            //return $this->redirect(array('action' => 'verVenta',$order_id));
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash("No se podían registrar los contadores análogos de fecha ".$counterDateString, 'default',['class' => 'error-message']);
          }
        }	
      }      
    }
		else {
      $hoses=$this->Hose->find('all',[
        'conditions'=>[
          'Hose.bool_active'=>true,
          'Hose.island_id'=>$enterpriseIslandIds,
        ],
        'contain'=>[
          'HoseCounter'=>[
            'conditions'=>[
              'counter_date'=>$counterDate,
              'HoseCounter.shift_id'=>$shiftId,
              'HoseCounter.enterprise_id'=>$enterpriseId,
            ]
          ]
        ],
      ]);
      //pr($hoses);
      foreach ($hoses as $hose){
        if (!empty($hose['HoseCounter'])){
          $requestHoseCounters[$hose['Hose']['id']]=$hose['HoseCounter'][0]['counter_value'];
        }
      }
      //pr($requestHoseCounters);
      if (empty($requestHoseCounters)){
        $boolEditingMode=true;
      }
      else {
        $boolEditingToggleVisible=true;
      }
    }
    
    //pr($requestHoseCounters);
    //pr($counterDate);
    $this->set(compact('boolEditingMode'));
    $this->set(compact('boolEditingToggleVisible'));
    $this->set(compact('counterDate'));
		$this->set(compact('enterpriseId'));
    $this->set(compact('shiftId'));
		$this->set(compact('requestHoseCounters'));
		
		$islands=$this->Island->find('all',[
      'conditions'=>['Island.id'=>$enterpriseIslandIds,],
      'contain'=>[
        'Hose'=>[
          'Product',
          'HoseCounter'=>[
            'conditions'=>[
              'HoseCounter.counter_date <'=>$counterDate,
              'HoseCounter.shift_id'=>$shiftId,
              'HoseCounter.enterprise_id'=>$enterpriseId,
            ],
            'order'=>'HoseCounter.counter_date DESC',
            'limit'=>1,
          ],
          'StockMovement'=>[
            'conditions'=>[
             'StockMovement.movement_date'=>$counterDate,
             'StockMovement.enterprise_id'=>$enterpriseId,
            ]
          ],
        ],
      ],
      'order'=>'Island.name',
		]);
    //pr($islands);
    /*
    for ($i=0;$i<count($islands);$i++){
      $islandFuelTotal=0;
      for ($h=0;$h<count($islands[$i]['Hose']);$h++){
        $hoseFuelTotal=0;
        if (!empty($islands[$i]['Hose'][$h]['StockMovement'])){
          foreach ($islands[$i]['Hose'][$h]['StockMovement'] as $stockMovement){
            $hoseFuelTotal+=$stockMovement['product_quantity'];
          }
        }
        $islands[$i]['Hose'][$h]['fuel_total']=$hoseFuelTotal;
        $islandFuelTotal+=$hoseFuelTotal;
      }
      $islands[$i]['fuel_total']=$islandFuelTotal;
    }
    */
    //pr($islands);
		$this->set(compact('islands'));
    
    $this->loadModel('Shift');
		$shifts=$this->Shift->find('list',[
			'conditions'=>['Shift.bool_active'=>true],
      'order'=>'Shift.name',
		]);
		$this->set(compact('shifts'));
  }

	public function eliminar($id = null) {
		$this->HoseCounter->id = $id;
		if (!$this->HoseCounter->exists()) {
			throw new NotFoundException(__('Invalid hose counter'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->HoseCounter->delete()) {
			$this->Session->setFlash(__('The hose counter has been deleted.'));
		} else {
			$this->Session->setFlash(__('The hose counter could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
