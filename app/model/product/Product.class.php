<?php

use Adianti\Database\TRecord;

class Product extends TRecord
{
    const TABLENAME = 'products';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    private $category;

    public function __construct($id = null)
    {
        parent::__construct($id)   ;

        parent::addAttribute('name');
        parent::addAttribute('quantity');
        parent::addAttribute('price');
        parent::addAttribute('category_id');
    }

    public function get_category()
    {
        if ($this->category == null) {
            $this->category = new Category($this->category_id);
        }

        return $this->category;
    }

    public function saveItem($data)
    {
        try {
            TTransaction::open('sample');
            
            $this->name = $data->name;
            $this->quantity = $data->quantity;
            $this->price = $data->price;
            $this->category_id = $data->category_id;
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
            $item->quantity = $data->quantity;
            $item->price = $data->price;
            $item->category_id = $data->category_id;
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