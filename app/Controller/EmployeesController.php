<?php
App::uses('AppController', 'Controller');

class EmployeesController extends AppController {

	public $components = array('Paginator','RequestHandler');

	public function resumen() {
		$this->loadModel('EmployeeHoliday');
    $this->Employee->recursive = -1;
		
    
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
    
    $employeeConditions=[
      'Employee.bool_active'=>true,
      'Employee.enterprise_id'=>$enterpriseId,
    ];
    
		$employeeCount=$this->Employee->find('count',[
			'fields'=>['Employee.id'],
			'conditions'=>$employeeConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions'=>$employeeConditions,
      'contain'=>['Enterprise'],
			'order' => ['Employee.bool_active'=>'DESC','Employee.last_name'=>'ASC'],
			'limit'=>($employeeCount == 0?1:$employeeCount),
			
		];
		$employees = $this->Paginator->paginate('Employee');
		
		$nowDate=new DateTime();
		//$newYearDate=new DateTime(date('Y-1-1'));
		for ($e=0;$e<count($employees);$e++){
			$workingDays=0;
			/*
			if ($employees[$e]['Employee']['starting_date']>date('Y-01-01')){
				$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
				$daysThisYear=$nowDate->diff($startingDate);
			}
			else {
				$daysThisYear=$nowDate->diff($newYearDate);
			}
			*/
			$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
			$endingDate=new DateTime($employees[$e]['Employee']['ending_date']);
      if ($endingDate<$nowDate){
        $daysWorked=$endingDate->diff($startingDate);
      }
      else {
        $daysWorked=$nowDate->diff($startingDate);
      }
			$workingDays=$daysWorked->days;
			$holidaysEarned=2.5*$workingDays/30;
			$employees[$e]['Employee']['holidays_earned']=$holidaysEarned;
			$this->EmployeeHoliday->virtualFields['total_holidays_taken']=0;
			$employeeHolidays=$this->EmployeeHoliday->find('all',array(
				'fields'=>array(
					'SUM(days_taken) AS EmployeeHoliday__total_holidays_taken', 
				),
				'conditions'=>array(
					'EmployeeHoliday.employee_id'=>$employees[$e]['Employee']['id'],
					//'EmployeeHoliday.holiday_date >='=>date('Y-01-01'),
				),
			));
			//pr($employeeHolidays);
			if (!empty($employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'])){
				$holidaysTaken=$employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'];
			}
			else {
				$holidaysTaken=0;
			}
			$employees[$e]['Employee']['holidays_taken']=$holidaysTaken;
		}
		//pr($employees);
		$this->set(compact('employees'));
		
    $aco_name="Employees/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Employees/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Employees/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
    
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function resumenEmpleadosDesactivados() {
		$this->Employee->recursive = -1;
		//$employees=$this->Paginator->paginate();
		
		$this->loadModel('EmployeeHoliday');
		
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
    
    $employeeConditions=[
      'Employee.bool_active'=>false,
      'Employee.enterprise_id'=>$enterpriseId,
    ];
    
		$employeeCount=$this->Employee->find('count',[
			'fields'=>['Employee.id'],
			'conditions'=>$employeeConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions'=>$employeeConditions,
      'contain'=>['Enterprise'],
			'order' => ['Employee.bool_active'=>'DESC','Employee.last_name'=>'ASC'],
			'limit'=>($employeeCount == 0?1:$employeeCount),
		];
		$employees = $this->Paginator->paginate('Employee');
		
		$nowDate=new DateTime();
		//$newYearDate=new DateTime(date('Y-1-1'));
		for ($e=0;$e<count($employees);$e++){
			$workingDays=0;
			/*
			if ($employees[$e]['Employee']['starting_date']>date('Y-01-01')){
				$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
				$daysThisYear=$nowDate->diff($startingDate);
			}
			else {
				$daysThisYear=$nowDate->diff($newYearDate);
			}
			*/
			$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
			$endingDate=new DateTime($employees[$e]['Employee']['ending_date']);
      if ($endingDate<$nowDate){
        $daysWorked=$endingDate->diff($startingDate);
      }
      else {
        $daysWorked=$nowDate->diff($startingDate);
      }
			$workingDays=$daysWorked->days;
			$holidaysEarned=2.5*$workingDays/30;
			$employees[$e]['Employee']['holidays_earned']=$holidaysEarned;
			$this->EmployeeHoliday->virtualFields['total_holidays_taken']=0;
			$employeeHolidays=$this->EmployeeHoliday->find('all',array(
				'fields'=>array(
					'SUM(days_taken) AS EmployeeHoliday__total_holidays_taken', 
				),
				'conditions'=>array(
					'EmployeeHoliday.employee_id'=>$employees[$e]['Employee']['id'],
					//'EmployeeHoliday.holiday_date >='=>date('Y-01-01'),
				),
			));
			//pr($employeeHolidays);
			if (!empty($employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'])){
				$holidaysTaken=$employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'];
			}
			else {
				$holidaysTaken=0;
			}
			$employees[$e]['Employee']['holidays_taken']=$holidaysTaken;
		}
		//pr($employees);
		$this->set(compact('employees'));
    
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function detalle($id = null) {
		if (!$this->Employee->exists($id)) {
			throw new NotFoundException(__('Empleado inválido'));
		}
    
    $this->loadModel('EmployeeHoliday');
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
		elseif (!empty($this->params['named']['sort'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}

		if (!isset($startDate)){
			$startDate = date("Y-01-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
		
		$options = [
			'conditions' => ['Employee.id' => $id],
			'contain'=>[
        'EmployeeHoliday'=>[
					'conditions' => [
						'EmployeeHoliday.employee_id'=> $id,
						'EmployeeHoliday.holiday_date >='=> $startDate,
						'EmployeeHoliday.holiday_date <'=> $endDatePlusOne,
					],
					'order'=>'holiday_date ASC',
					'HolidayType',
				],
        'Enterprise'
			],
		];
		$employee=$this->Employee->find('first', $options);
		$this->set(compact('employee'));
		
    $startingDateTime=new DateTime($employee['Employee']['starting_date']);
    $endingDateTime=new DateTime($employee['Employee']['ending_date']);
    $yearArray=$this->EmployeeHoliday->getHolidayYearArray($employee['Employee']['id'],$startingDateTime,$endingDateTime);
    //pr($yearArray);
    $this->set(compact('yearArray'));
    
		$filename='Hoja_Vacaciones_'.$employee['Employee']['first_name']."_".$employee['Employee']['last_name']."_".date('d_m_Y');
		$this->set(compact('filename'));
    
    $userRole = $this->Auth->User('role_id');
    $this->set(compact('userRole'));
    
    $aco_name="Employees/crear";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
    
    $aco_name="Employees/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
    
    $aco_name="Employees/eliminar";		
		$bool_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_delete_permission'));
    
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function verPdfHojaVacaciones($startDate=null,$endDate=null,$employee_id=null){
		if ($startDate==null){
			$startDateString=$_SESSION['startDate'];
		}
		else {
			$startDateString=$startDate;
		}
		$startDate=date( "Y-m-d", strtotime($startDateString));
		if ($endDate==null){
			$endDateString=$_SESSION['endDate'];
		}
		else {
			$endDateString=$endDate;
		}
		$endDate=date("Y-m-d",strtotime($endDateString."+1 days"));
		$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		
		$options = array(
			'conditions' => array('Employee.' . $this->Employee->primaryKey => $employee_id),
			'contain'=>array(
				'EmployeeHoliday'=>array(
					'conditions' => array(
						'EmployeeHoliday.employee_id'=> $employee_id,
						'EmployeeHoliday.holiday_date >='=> $startDate,
						'EmployeeHoliday.holiday_date <'=> $endDatePlusOne,
					),
					'order'=>'holiday_date ASC',
					'HolidayType',
				)
			),
		);
		$employee=$this->Employee->find('first', $options);
		$this->set(compact('employee'));
		
		$this->set(compact('startDate','endDate','endDatePlusOne','accountingCodes','statusFlows','results'));	
		
		$filename='Hoja_Vacaciones_'.$employee['Employee']['first_name']."_".$employee['Employee']['last_name']."_".date('d_m_Y');
		$this->set(compact('filename'));
	}
	
	public function crear() {
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
    
		if ($this->request->is('post')) {
      $employeesInSameEnterpriseWithSameName=$this->Employee->find('list',[
        'conditions'=>[
          'first_name'=>$this->request->data['Employee']['first_name'],
          'last_name'=>$this->request->data['Employee']['last_name'],
          'enterprise_id'=>$this->request->data['Employee']['enterprise_id'],
        ]
      ]);
      if  (empty($this->request->data['Employee']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del empleado.'), 'default',['class' => 'error-message']);
      }
      elseif (!empty($employeesInSameEnterpriseWithSameName)){
        $this->Session->setFlash(__('Ya existe un empleado con este nombre para esta gasolinera.  No se guardó el empleado.'), 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Employee->getDataSource();
        $datasource->begin();
        try {
          $this->Employee->create();
          if (!$this->Employee->save($this->request->data)) {
            echo "Problema guardando el empleado";
            pr($this->validateErrors($this->Employee));
            throw new Exception();
          }
          $employee_id=$this->Employee->id;
          if (!empty($this->request->data['Document']['url_image'][0]['tmp_name'])){
            $imageOK=$this->uploadFiles('employeeimages/'.$employee_id,$this->request->data['Document']['url_image']);
            //echo "image OK<br/>";
            //pr($imageOK);
            if (array_key_exists('urls',$imageOK)){
              $this->request->data['Employee']['url_image']=$imageOK['urls'][0];
            }
          }
          if (!$this->Employee->save($this->request->data)) {
            echo "Problema guardando el empleado con su imagen";
            pr($this->validateErrors($this->Employee));
            throw new Exception();
          }
          $datasource->commit();
          $this->recordUserAction($this->Employee->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se creó el empleado ".$this->request->data['Employee']['first_name']." ".$this->request->data['Employee']['last_name']);
          $this->Session->setFlash(__('Se guardó el empleado.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);
          $this->Session->setFlash(__('No se guardó el empleado.'), 'default',['class' => 'error-message']);
        }
      }
		}
    
    $aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function editar($id = null) {
		if (!$this->Employee->exists($id)) {
			throw new NotFoundException(__('Invalid employee'));
		}
    
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
    
		if ($this->request->is(['post', 'put'])) {
      if  (empty($this->request->data['Employee']['enterprise_id'])){
         $this->Session->setFlash(__('Se debe especificar la empresa del empleado.'), 'default',['class' => 'error-message']);
      }
      else {
        $datasource=$this->Employee->getDataSource();
        try {
          $datasource->begin();
          
          $this->Employee->id=$id;
          $employee_id=$id;
          if (!empty($this->request->data['Document']['url_image'][0]['tmp_name'])){
            $imageOK=$this->uploadFiles('employeeimages/'.$employee_id,$this->request->data['Document']['url_image']);
            //echo "image OK<br/>";
            //pr($imageOK);
            if (array_key_exists('urls',$imageOK)){
              $this->request->data['Employee']['url_image']=$imageOK['urls'][0];
            }
          }
          if (!$this->Employee->save($this->request->data)) {
            echo "Problema guardando el empleado";
            pr($this->validateErrors($this->Employee));
            throw new Exception();
          }
          $datasource->commit();
          $this->recordUserAction($this->Employee->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se creó el empleado ".$this->request->data['Employee']['first_name']." ".$this->request->data['Employee']['last_name']);
          $this->Session->setFlash(__('Se guardó el empleado.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);
          $this->Session->setFlash(__('No se guardó el empleado.'), 'default',['class' => 'error-message']);
        }
      }
			
		} 
		else {
			$options = ['conditions' => ['Employee.id' => $id]];
			$this->request->data = $this->Employee->find('first', $options);
		}
    
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminar($id = null) {
		$this->Employee->id = $id;
		if (!$this->Employee->exists()) {
			throw new NotFoundException(__('Empleado inválido'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Employee->delete()) {
			$this->Session->setFlash(__('The employee has been deleted.'));
		} else {
			$this->Session->setFlash(__('The employee could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
