<?php
App::uses('AppController', 'Controller');

class ShiftsController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    $enterpriseId=0;
    if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
      $enterpriseId = $_SESSION['enterpriseId'];
		}
    if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    
    $this->Shift->recursive = -1;
		$shiftCount=$this->Shift->find('count');
		$this->Paginator->settings = [
      'conditions'=>[
        'Shift.enterprise_id'=>$enterpriseId,
      ],
      'contain'=>['Enterprise'],
			'order'=>'Shift.bool_active DESC, Shift.name ASC',
			'limit'=>($shiftCount!=0?$shiftCount:1)
		];
    $this->set('shifts', $this->Paginator->paginate());
    
    $aco_name="Shifts/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Shifts/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function detalle($id = null) {
		if (!$this->Shift->exists($id)) {
			throw new NotFoundException(__('Invalid shift'));
		}

		$this->loadModel('Product');
    $this->loadModel('ProductType');
    
    $this->loadModel('Operator');
		
    $this->Product->recursive=-1;
    $this->ProductType->recursive=-1;
    $this->Operator->recursive=-1;
    $this->Shift->recursive=-1;
    
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
    $options = [
			'conditions' => ['Shift.id' => $id],
			'contain'=>[
				'Enterprise'
			]
		];
		$shift=$this->Shift->find('first', $options);
		
		$operators=$this->Operator->find('all',['fields'=>['Operator.id','Operator.name']]);
		$operatorCounter=0;
		
		$this->set(compact('operator','startDate','endDate'));
		
		$this->set(compact('shift'));
		
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
		
		$otherShifts=$this->Shift->find('all',[
			'fields'=>['Shift.id','Shift.name'],
			'conditions'=>['Shift.id !='=>$id],
		]);
		$this->set(compact('otherShifts'));
   
		$aco_name="Shifts/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Shifts/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function crear() {
     $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($this->request->is('post')) {
      if  (empty($this->request->data['Shift']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del turno.'), 'default',['class' => 'error-message']);
      }
      else {
        $this->Shift->create();
        if ($this->Shift->save($this->request->data)) {
          $this->recordUserAction($this->Shift->id,null,null);
          $this->Session->setFlash(__('The shift has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        else {
          $this->Session->setFlash(__('The shift could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		}
    
    $enterpriseId=0;
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

	public function editar($id = null) {
		if (!$this->Shift->exists($id)) {
			throw new NotFoundException(__('Invalid shift'));
		}
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($this->request->is(['post', 'put'])) {
      if  (empty($this->request->data['Shift']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del turno.'), 'default',['class' => 'error-message']);
      }
      else {
        if ($this->Shift->save($this->request->data)) {
          $this->recordUserAction();
          $this->Session->setFlash(__('The shift has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'index']);
        } 
        else {
          $this->Session->setFlash(__('The shift could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		} 
		else {
			$options = ['conditions' => ['Shift.id' => $id]];
			$this->request->data = $this->Shift->find('first', $options);
		}
    
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
		/*
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
    */
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Shift->id = $id;
		if (!$this->Shift->exists()) {
			throw new NotFoundException(__('Invalid shift'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Shift->delete()) {
			$this->Session->setFlash(__('The shift has been deleted.'));
		} else {
			$this->Session->setFlash(__('The shift could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
}
