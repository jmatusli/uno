<?php
App::uses('AppController', 'Controller');

class ClosingDatesController extends AppController {

	public $components = ['Paginator'];


	public function resumen() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');

    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
    //if ($userRoleId == ROLE_ADMIN && !empty($_SESSION['enterpriseId'])){
    //  $enterpriseId = $_SESSION['enterpriseId'];
		//}
    if ($this->request->is('post')) {
			$enterpriseId=$this->request->data['Report']['enterprise_id'];
		}
		
    $enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    //pr($enterprises);
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    //$_SESSION['enterpriseId']=$enterpriseId;
    $this->set(compact('enterpriseId'));
    $this->set(compact('enterprises'));
    
    $closingDateConditions=[];
    if ($enterpriseId > 0){
      $closingDateConditions['ClosingDate.enterprise_id']=$enterpriseId;
    }
		$closingDates=$this->ClosingDate->find('all',[
      'conditions'=>$closingDateConditions,
      'contain'=>['Enterprise'],
			'order'=>'closing_date DESC'
		]);
		$this->set(compact('closingDates'));
	}

	public function detalle($id = null) {
		if (!$this->ClosingDate->exists($id)) {
			throw new NotFoundException(__('Invalid closing date'));
		}
		$options = [
      'conditions' => ['ClosingDate.id'=> $id],
      'contain'=>['Enterprise'],
    ];
		$this->set('closingDate', $this->ClosingDate->find('first', $options));
	}

	public function crear() {
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
		$firstDateOfCurrentMonth = date("Y-m-01");
		$proposedClosingDate=date( "Y-m-d", strtotime( $firstDateOfCurrentMonth."-1 days" ) );
    
    $enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
     
		$enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
    
		if ($this->request->is('post')) {
			$this->ClosingDate->create();
			if (!$this->ClosingDate->save($this->request->data)) {
        pr($e);  
        $this->Session->setFlash(__('The closing date could not be saved. Please, try again.'),'default',['class' => 'error-message']);  
      }
      $this->recordUserAction($this->ClosingDate->id,null,null);
      $this->Session->setFlash(__('The closing date has been saved.'),'default',['class' => 'success']);
      return $this->redirect(['action' => 'resumen']);
		}
		$this->set(compact('proposedClosingDate'));
	}

	public function editar($id = null) {
    if (!$this->ClosingDate->exists($id)) {
			throw new NotFoundException(__('Invalid closing date'));
		}
    
    $this->loadModel('Enterprise');
    $this->loadModel('EnterpriseUser');
    
		$enterpriseId=0;
    
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
     
		$enterprises=$this->EnterpriseUser->getEnterpriseListForUser($loggedUserId);
    $this->set(compact('enterprises'));
    if (count($enterprises) == 1){
      $enterpriseId=array_keys($enterprises)[0];
    }
    $this->set(compact('enterpriseId'));
		
		if ($this->request->is(['post', 'put'])) {
      $this->ClosingDate->id=$id;
			if (!$this->ClosingDate->save($this->request->data)) {
        pr($e);  
        $this->Session->setFlash(__('The closing date could not be saved. Please, try again.'),'default',['class' => 'error-message']);  
      }  
      $this->recordUserAction();
      $this->Session->setFlash(__('The closing date has been saved.'),'default',['class' => 'success']);
      return $this->redirect(['action' => 'resumen']);    
    } 
    else {
			$options = [
      'conditions' => ['ClosingDate.id'=> $id],
        'contain'=>['Enterprise'],
      ];
      $this->request->data = $this->ClosingDate->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ClosingDate->id = $id;
		if (!$this->ClosingDate->exists()) {
			throw new NotFoundException(__('Invalid closing date'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClosingDate->delete()) {
			$this->Session->setFlash(__('The closing date has been deleted.'),'default',['class' => 'success']);
		} else {
			$this->Session->setFlash(__('The closing date could not be deleted. Please, try again.'),'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
