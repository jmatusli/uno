<?php
App::uses('AppController', 'Controller');

class ClientEnterprisesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->ClientEnterprise->recursive = -1;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		
		$clientEnterpriseCount=	$this->ClientEnterprise->find('count', array(
			'fields'=>array('ClientEnterprise.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($clientEnterpriseCount!=0?$clientEnterpriseCount:1),
		);

		$clientEnterprises = $this->Paginator->paginate('ClientEnterprise');
		$this->set(compact('clientEnterprises'));
	}

	public function view($id = null) {
		if (!$this->ClientEnterprise->exists($id)) {
			throw new NotFoundException(__('Invalid client user'));
		}
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$options = array('conditions' => array('ClientEnterprise.' . $this->ClientEnterprise->primaryKey => $id));
		$this->set('clientEnterprise', $this->ClientEnterprise->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ClientEnterprise->create();
			if ($this->ClientEnterprise->save($this->request->data)) {
				$this->Session->setFlash(__('The client user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The client user could not be saved. Please, try again.'));
			}
		}
		$clients = $this->ClientEnterprise->Client->find('list');
		$users = $this->ClientEnterprise->User->find('list');
		$this->set(compact('clients', 'users'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->ClientEnterprise->exists($id)) {
			throw new NotFoundException(__('Invalid client user'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ClientEnterprise->save($this->request->data)) {
				$this->Session->setFlash(__('The client user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The client user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ClientEnterprise.' . $this->ClientEnterprise->primaryKey => $id));
			$this->request->data = $this->ClientEnterprise->find('first', $options);
		}
		$clients = $this->ClientEnterprise->Client->find('list');
		$users = $this->ClientEnterprise->User->find('list');
		$this->set(compact('clients', 'users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ClientEnterprise->id = $id;
		if (!$this->ClientEnterprise->exists()) {
			throw new NotFoundException(__('Invalid client user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClientEnterprise->delete()) {
			$this->Session->setFlash(__('The client user has been deleted.'));
		} else {
			$this->Session->setFlash(__('The client user could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
