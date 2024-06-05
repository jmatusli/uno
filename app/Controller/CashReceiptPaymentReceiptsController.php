<?php
App::uses('AppController', 'Controller');
/**
 * CashReceiptPaymentReceipts Controller
 *
 * @property CashReceiptPaymentReceipt $CashReceiptPaymentReceipt
 * @property PaginatorComponent $Paginator
 */
class CashReceiptPaymentReceiptsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->CashReceiptPaymentReceipt->recursive = -1;
		
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
		
		$cashReceiptPaymentReceiptCount=	$this->CashReceiptPaymentReceipt->find('count', array(
			'fields'=>array('CashReceiptPaymentReceipt.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($cashReceiptPaymentReceiptCount!=0?$cashReceiptPaymentReceiptCount:1),
		);

		$cashReceiptPaymentReceipts = $this->Paginator->paginate('CashReceiptPaymentReceipt');
		$this->set(compact('cashReceiptPaymentReceipts'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->CashReceiptPaymentReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt payment receipt'));
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
		$options = array('conditions' => array('CashReceiptPaymentReceipt.' . $this->CashReceiptPaymentReceipt->primaryKey => $id));
		$this->set('cashReceiptPaymentReceipt', $this->CashReceiptPaymentReceipt->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->CashReceiptPaymentReceipt->create();
			if ($this->CashReceiptPaymentReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The cash receipt payment receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt payment receipt could not be saved. Please, try again.'));
			}
		}
		$cashReceipts = $this->CashReceiptPaymentReceipt->CashReceipt->find('list');
		$paymentReceipts = $this->CashReceiptPaymentReceipt->PaymentReceipt->find('list');
		$currencies = $this->CashReceiptPaymentReceipt->Currency->find('list');
		$this->set(compact('cashReceipts', 'paymentReceipts', 'currencies'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->CashReceiptPaymentReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt payment receipt'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->CashReceiptPaymentReceipt->save($this->request->data)) {
				$this->Session->setFlash(__('The cash receipt payment receipt has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The cash receipt payment receipt could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('CashReceiptPaymentReceipt.' . $this->CashReceiptPaymentReceipt->primaryKey => $id));
			$this->request->data = $this->CashReceiptPaymentReceipt->find('first', $options);
		}
		$cashReceipts = $this->CashReceiptPaymentReceipt->CashReceipt->find('list');
		$paymentReceipts = $this->CashReceiptPaymentReceipt->PaymentReceipt->find('list');
		$currencies = $this->CashReceiptPaymentReceipt->Currency->find('list');
		$this->set(compact('cashReceipts', 'paymentReceipts', 'currencies'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->CashReceiptPaymentReceipt->id = $id;
		if (!$this->CashReceiptPaymentReceipt->exists()) {
			throw new NotFoundException(__('Invalid cash receipt payment receipt'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->CashReceiptPaymentReceipt->delete()) {
			$this->Session->setFlash(__('The cash receipt payment receipt has been deleted.'));
		} else {
			$this->Session->setFlash(__('The cash receipt payment receipt could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
