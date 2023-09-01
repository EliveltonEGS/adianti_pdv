<?php

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class Sale extends TRecord
{
    const TABLENAME = 'sales';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id)   ;

        parent::addAttribute('total');
        parent::addAttribute('created_at');
    }

    public function saveSale($data)
    {
        try {
            TTransaction::open('sample');

            $this->total = $data->totalFinal;
            $this->created_at = date('Y-m-d H:i:s');
            $this->store();

            TTransaction::close();
        } catch (\Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function lastId()
    {
        try {
            TTransaction::open('sample');

            return $this->getLastID();
            
            TTransaction::close();
        } catch (\Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}