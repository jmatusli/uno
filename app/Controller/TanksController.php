<?php
App::uses('AppController', 'Controller');

class TanksController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->Tank->recursive = -1;
		$tankCount=$this->Tank->find('count');
		$this->Paginator->settings = [
      'contain'=>['Enterprise','Product'],
			'order'=>'Tank.bool_active DESC, Tank.name ASC',
			'limit'=>($tankCount!=0?$tankCount:1)
		];
		$this->set('tanks', $this->Paginator->paginate());
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Tanks/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Tanks/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Tanks/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));

	}

	public function detalle($id = null) {
		if (!$this->Tank->exists($id)) {
			throw new NotFoundException(__('Invalid tank'));
		}
		
		$this->loadModel('Product');
		
		$this->loadModel('Island');
    $this->loadModel('Shift');
    
		$this->Product->recursive=-1;
		
    $this->Island->recursive=-1;
    $this->Tank->recursive=-1;
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
			'conditions' => ['Tank.id'  => $id],
			'contain'=>[
        'Enterprise',
        'Product',
			]
		];
		
		$tank=$this->Tank->find('first', $options);
		//pr($tank);
	
		$this->set(compact('tank','startDate','endDate','soldProductsPerShift','visibleArray'));
		
		$this->Tank->recursive=-1;
		$otherTanks=$this->Tank->find('all',[
			'fields'=>['Tank.id','Tank.name'],
			'conditions'=>[
				'Tank.id !='=>$id,
				'Tank.bool_active'=>true,
			],
			'order'=>'Tank.name ASC',
		]);
		$this->set(compact('otherTanks'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Tanks/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Tanks/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Tanks/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));

	}

	public function crear() {
		if ($this->request->is('post')) {
      if  (empty($this->request->data['Tank']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del operador.'), 'default',['class' => 'error-message']);
      }
      else {
        $this->Tank->create();
        if ($this->Tank->save($this->request->data)) {
          $this->recordUserAction($this->Tank->id,null,null);
          $this->Session->setFlash(__('The tank has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        else {
          $this->Session->setFlash(__('The tank could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		}
    
    $enterprises=$this->Tank->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
   
    $products=$this->Tank->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('products'));
   
	}

	public function editar($id = null) {
		if (!$this->Tank->exists($id)) {
			throw new NotFoundException(__('Operador inválido'));
		}
		if ($this->request->is(['post', 'put'])) {
      if  (empty($this->request->data['Tank']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del operador.'), 'default',['class' => 'error-message']);
      }
      else {
        if ($this->Tank->save($this->request->data)) {
          $this->recordUserAction();
          $this->Session->setFlash(__('The tank has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        else {
          $this->Session->setFlash(__('The tank could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
      }
		} 
		else {
			$options = ['conditions' => ['Tank.id' => $id]];
			$this->request->data = $this->Tank->find('first', $options);
		}
    
    $enterprises=$this->Tank->Enterprise->find('list',['order'=>'Enterprise.company_name ASC']);
    $this->set(compact('enterprises'));
    
    $products=$this->Tank->Product->find('list',[
      'conditions'=>['Product.product_type_id'=>PRODUCT_TYPE_FUELS],
      'order'=>'Product.name ASC',
    ]);
    $this->set(compact('products'));
    
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Tank->id = $id;
		if (!$this->Tank->exists()) {
			throw new NotFoundException(__('Operador inválido'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Tank->delete()) {
			$this->Session->setFlash(__('The tank has been deleted.'));
		} else {
			$this->Session->setFlash(__('The tank could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
}
