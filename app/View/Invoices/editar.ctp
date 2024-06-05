<script>
  $(document).on('change','#InvoicePaymentModeId',function(){
    if ($(this).val() == <?php echo PAYMENT_MODE_CREDIT; ?>){
      $('#InvoiceClientId').closest('div').removeClass('hidden');
      $('#InvoiceInvoiceCode').removeAttr('readonly');
    }
    else {
      $('#InvoiceClientId').closest('div').addClass('hidden');
      $('#InvoiceInvoiceCode').attr('readonly',true);
    }
  });
  
  $(document).on('change','#InvoiceSubTotalPrice',function(){
    var subtotal=$(this).val();
    if (!isNaN(subtotal)){
      $('#InvoiceTotalPrice').val(subtotal);
    }
    else {
      $('#InvoiceSubTotalPrice').val(0);
      $('#InvoiceTotalPrice').val(0);
      alert('subtotal debe ser un número positivo!');
    }
  });
  
  $(document).ready(function(){
    $('#InvoiceInvoiceDateDay option:not(:selected)').attr('disabled', true);
    $('#InvoiceInvoiceDateMonth option:not(:selected)').attr('disabled', true);
    $('#InvoiceInvoiceDateYear option:not(:selected)').attr('disabled', true);
		$('select.fixed option:not(:selected)').attr('disabled', true);
    $('#InvoicePaymentModeId').trigger("change");
	});
</script>

<div class="invoices form fullwidth">
<?php 
  echo $this->Form->create('Invoice'); 
	echo '<fieldset>';
  echo '<div class="container-fluid">';
    echo '<div class="rows">';
      echo '<div class="col-sm-8">';
  
        echo '<legend>'.__('Edit Invoice').'</legend>';
        
        echo $this->Form->input('id');  
        //echo $this->Form->input('enterprise_id');
        echo $this->EnterpriseFilter->displayEnterpriseFilter($enterprises, $userRoleId,$enterpriseId);
        
        echo $this->Form->input('invoice_date',['type'=>'date','dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
        echo $this->Form->input('due_date',['type'=>'date','dateFormat'=>'DMY','minYear'=>2019,'maxYear'=>date('Y')]);
        
        echo $this->Form->input('payment_mode_id',['class'=>'paymentmode','class'=>'fixed']);
        echo $this->Form->input('invoice_code');
        
        echo $this->Form->input('shift_id',['empty'=>[0=>'-- Seleccione Turno --']]);
        echo $this->Form->input('operator_id',['empty'=>[0=>'-- Seleccione Operador --']]);
        //echo $this->Form->input('payment_receipt_id');
        
        echo $this->Form->input('client_id',['empty'=>[0=>'-- Seleccione Cliente --']]);
        echo $this->Form->input('currency_id',['default'=>CURRENCY_CS,'class'=>'fixed']);
        
        echo $this->Form->input('sub_total_price',['type'=>'decimal']);
        
        echo $this->Form->input('order_id',['type'=>'hidden',]);
        
        echo $this->Form->input('creating_user_id',['type'=>'hidden',]);
        
        echo $this->Form->input('bool_credit',['type'=>'hidden',]);
        echo $this->Form->input('bool_annulled',['type'=>'hidden',]);
        
        echo $this->Form->input('bool_iva',['type'=>'hidden',]);
        echo $this->Form->input('iva_price',['type'=>'hidden']);
        echo $this->Form->input('total_price',['type'=>'hidden']);
        
        echo $this->Form->input('bool_paid',['type'=>'hidden',]);
        echo $this->Form->input('bool_deposited',['type'=>'hidden',]);
        
        //echo $this->Form->input('bool_retention',['type'=>'hidden',]);
        //echo $this->Form->input('retention_amount',['type'=>'hidden',]);
        //echo $this->Form->input('retention_number',['type'=>'hidden',]);
      echo '</div>';
      echo '<div class="col-sm-4">';
        echo '<ul style="list-style:none;">';
          echo '<li>'.$this->Html->link(__('List Invoices'), ['action' => 'resumen']).'</li>';
          echo '<li>'.$this->Html->link(__("List Clients"), ["controller" => "third_parties", "action" => "resumenClientes"]).'</li>';
        echo '</ul>';
      echo '</div>';      
    echo '</div>';
  echo '</div>';
	echo '</fieldset> ';
  echo $this->Form->end(__('Submit')); 
  
?>
</div>