<?php
App::uses('AppController', 'Controller');

class UnitsController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->Unit->recursive = -1;
		$unitCount=$this->Unit->find('count');
		$this->Paginator->settings = [
      'contain'=>['TargetUnit'],
			'order'=>'Unit.name ASC',
			'limit'=>($unitCount!=0?$unitCount:1)
		];
		$this->set('units', $this->Paginator->paginate());
    
    $aco_name="Units/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Units/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Units/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));

	}

	public function detalle($id = null) {
		if (!$this->Unit->exists($id)) {
			throw new NotFoundException(__('Invalid unit'));
		}
		
    $this->Unit->recursive=-1;
    
    $options = [
			'conditions' => ['Unit.id'  => $id],
			'contain'=>[
        'TargetUnit',
			]
		];
		
		$unit=$this->Unit->find('first', $options);
		//pr($unit);
	
		$this->set(compact('unit'));
		
		$this->Unit->recursive=-1;
		$otherUnits=$this->Unit->find('all',[
			'fields'=>['Unit.id','Unit.name'],
			'conditions'=>[
				'Unit.id !='=>$id,
			],
			'order'=>'Unit.name ASC',
		]);
		$this->set(compact('otherUnits'));
    
    $aco_name="Units/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Units/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Units/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));

	}

	public function crear() {
		if ($this->request->is('post')) {      
      $this->Unit->create();
      if ($this->Unit->save($this->request->data)) {
        $this->recordUserAction($this->Unit->id,null,null);
        $this->Session->setFlash(__('The unit has been saved.'),'default',['class' => 'success']);
        return $this->redirect(['action' => 'resumen']);
      } 
      else {
        $this->Session->setFlash(__('The unit could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
      }
		}
    
    $targetUnits=$this->Unit->find('list',[
      'order'=>'Unit.name ASC'
    ]);
    $this->set(compact('targetUnits'));
	}

	public function editar($id = null) {
		if (!$this->Unit->exists($id)) {
			throw new NotFoundException(__('Operador inválido'));
		}
		if ($this->request->is(['post', 'put'])) {
      if ($this->Unit->save($this->request->data)) {
        $this->recordUserAction();
        $this->Session->setFlash(__('The unit has been saved.'),'default',['class' => 'success']);
        return $this->redirect(['action' => 'resumen']);
      } 
      else {
        $this->Session->setFlash(__('The unit could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
      }
    }
		else {
			$options = ['conditions' => ['Unit.id' => $id]];
			$this->request->data = $this->Unit->find('first', $options);
		}
    
    $targetUnits=$this->Unit->find('list',[
      'conditions'=>['Unit.id !=' => $id],
      'order'=>'Unit.name ASC'
    ]);
    $this->set(compact('targetUnits'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Unit->id = $id;
		if (!$this->Unit->exists()) {
			throw new NotFoundException(__('Operador inválido'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Unit->delete()) {
			$this->Session->setFlash(__('The unit has been deleted.'));
		} else {
			$this->Session->setFlash(__('The unit could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}
}
