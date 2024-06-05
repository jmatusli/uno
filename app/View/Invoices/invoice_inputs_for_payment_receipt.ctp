<?php 
  if (empty($invoices)){
    echo $this->Form->input('Invoice.total_amount',['value'=>0,'type'=>'hidden','class'=>'invoiceTotalAmount']);
  }
  else {
    $invoiceList='';
    $invoiceInputs='';
    $invoiceTotalAmount=0;
    $i=0;
    
    switch ($paymentModeId){
      case PAYMENT_MODE_CARD_BAC:
      case PAYMENT_MODE_CARD_BANPRO:
        foreach ($invoices as $invoice){
          $invoiceList.=$invoice['Invoice']['invoice_code'];
          $invoiceInputs.=$this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.PaymentReceipt.'.$rowCounter.'.InvoiceData.Invoice.'.$i.'.id',['value'=>$invoice['Invoice']['id'],'type'=>'hidden','class'=>'invoice']);
          $invoiceTotalAmount+=$invoice['Invoice']['sub_total_price'];
          $i++;
          if ($i<count($invoices)){
            $invoiceList.=', ';
          }
        }
        echo '<span class="invoiceSpan">'.$invoiceList.'</span>';
        echo $invoiceInputs;
        echo $this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.PaymentReceipt.'.$rowCounter.'.InvoiceData.invoice_list',['value'=>$invoiceList,'type'=>'hidden','class'=>'invoiceList']);
        echo $this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.PaymentReceipt.'.$rowCounter.'.InvoiceData.invoice_total_amount',['value'=>$invoiceTotalAmount,'type'=>'hidden','class'=>'invoiceTotalAmount']);  
        break;
      case PAYMENT_MODE_CREDIT:
        foreach ($invoices as $invoice){
          $invoiceList.=$invoice['Invoice']['invoice_code'];
          $invoiceInputs.=$this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.Credit.'.$rowCounter.'.InvoiceData.Invoice.'.$i.'.id',['value'=>$invoice['Invoice']['id'],'type'=>'hidden','class'=>'invoice']);
          $invoiceTotalAmount+=$invoice['Invoice']['sub_total_price'];
          $i++;
          if ($i<count($invoices)){
            $invoiceList.=', ';
          }
        }
        echo '<span>'.$invoiceList.'</span>';
        echo $invoiceInputs;
        echo $this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.Credit.'.$rowCounter.'.InvoiceData.invoice_list',['value'=>$invoiceList,'type'=>'hidden','class'=>'invoiceList']);
        echo $this->Form->input('Shift.'.$shiftId.'.Operator.'.$operatorId.'.Credit.'.$rowCounter.'.InvoiceData.invoice_total_amount',['value'=>$invoiceTotalAmount,'type'=>'hidden','class'=>'invoiceTotalAmount']);  
        break;
      
      
      
      
      
      
      
      
      
      
      
      
      
      
    }
    
  }