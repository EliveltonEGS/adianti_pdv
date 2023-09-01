<?php

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Widget\Dialog\TMessage;

class ItemSale extends TRecord
{
    const TABLENAME = 'item_sales';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    private $sale;
    private $product;

    public function __construct($id = null)
    {
        parent::__construct($id)   ;

        parent::addAttribute('quantity');
        parent::addAttribute('price');
        parent::addAttribute('sale_id');
        parent::addAttribute('product_id');
    }

    public function get_sale()
    {
        if ($this->sale == null) {
            $this->sale = new Sale($this->sale_id);
        }

        return $this->sale;
    }

    public function get_product()
    {
        if ($this->product == null) {
            $this->product = new Product($this->product_id);
        }

        return $this->product;
    }

    public function saveItemsSale($saleId, $data)
    {
        try {
            TTransaction::open('sample');

            foreach ($data as $item) {
                $this->create([
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'sale_id' => $saleId,
                    'product_id' => $item->product_id
                ]);
            }
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::close();
        }
    }
}