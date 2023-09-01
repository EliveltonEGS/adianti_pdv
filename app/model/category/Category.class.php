<?php

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class Category extends TRecord
{
    const TABLENAME = 'categories';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id)   ;

        parent::addAttribute('name');
    }

    public function saveItem($data)
    {
        try {
            TTransaction::open('sample');
            
            $this->name = $data->name;
            $this->store();

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function updateItem($data)
    {
        try {
            TTransaction::open('sample');
            
            $item = $this->find($data->id);
            $item->name = $data->name;
            $item->store();

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function getAllItems()
    {
        try {
            TTransaction::open('sample');
            
            return $this->all();
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function getById($id)
    {
        try {
            TTransaction::open('sample');
            
            return $this->find($id);
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function deleteById($id)
    {
        try {
            TTransaction::open('sample');
            
            $item = $this->find($id);
            $item->delete();
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}