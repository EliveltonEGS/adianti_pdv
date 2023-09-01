<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Widget\Wrapper\TQuickGrid;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class PdvView extends TPage
{
    private $form;
    private $datagrid;
    private static $formName = 'form_pdv';

    private $product;
    private $sale;
    private $itemSale;

    private $totalVenda;

    public function __construct()
    {
        parent::__construct();
        $this->product  = new Product();
        $this->sale = new Sale();
        $this->itemSale = new ItemSale();

        $this->totalVenda = 0;

        $panel = new TPanelGroup('Products');

        //start form
        $this->form = new BootstrapFormBuilder(self::$formName);
        $this->form->setFormTitle('Add Items');

        $product_id = new TDBCombo('product_id', 'sample', 'Product', 'id', 'name', 'name asc');
        $quantity = new TEntry('quantity');
        $price = new TEntry('price');
        $totalItem = new TEntry('total_item');

        $price->setEditable(false);
        $totalItem->setEditable(false);

        $product_id->enableSearch();

        $product_id->setChangeAction(new TAction([$this, 'onChangePriceProduto']));
        $quantity->setExitAction(new TAction([$this, 'onChangeTotalItem']));

        $this->form->addAction('Add', new TAction([$this, 'onAddItem']), 'fa:plus');
        $this->form->addAction('Empty', new TAction([$this, 'onCleanItems']), 'far:trash-alt');
        $this->form->addAction('Save', new TAction([$this, 'onSave']), 'fa:save');

        $this->form->addFields([new TLabel('Product')], [$product_id], [new TLabel('Quantity')], [$quantity]);
        $this->form->addFields([new TLabel('Price')], [$price], [new TLabel('Total Item')], [$totalItem]);
        //end form

        //add form in panel
        // $panel->add($this->form);

        //start line division
        $label = new TLabel('All Items in Cart', '#7D78B6', 12, 'bi');
        $label->style='color: #000;text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $panel->add($label);
        //end line division

        //start quick datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TQuickGrid());
        $this->datagrid->width = '100%';

        $this->datagrid->addQuickColumn('Remove', 'delete', 'center');
        // $this->datagrid->addQuickColumn('ID', 'product_id', 'center');
        $this->datagrid->addQuickColumn('Name', 'name', 'center');
        $this->datagrid->addQuickColumn('Quantity', 'quantity', 'center');
        $this->datagrid->addQuickColumn('Price', 'price', 'center');
        // $this->datagrid->addQuickColumn('Total', 'total', 'center');
        $this->totalVenda = $this->datagrid->addQuickColumn('Total', '= ({quantity} * {price})', 'center');

        $this->totalVenda->setTotalFunction( function($values) {
            return array_sum((array) $values);
        });

        $this->datagrid->createModel();
        //end quick datagrid

        //add datagrid in panel
        // $panel->add($this->datagrid);
        //container 
        $container = new TVBox();
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($this->datagrid);

        parent::add($container);
    }

    public static function onChangePriceProduto($param = null)
    {
        $product = new Product();
        $item = $product->getById($param['product_id']);

        $objet = new stdClass();
        $objet->price = $item->price;

        TForm::sendData(self::$formName, $objet);
    }

    public static function onChangeTotalItem($param = null)
    {
        $object = new stdClass();
        $object->total_item = $param['quantity'] * $param['price'];
        TForm::sendData(self::$formName, $object);
    }

    public function onAddItem($param = null)
    {
        $data = $this->form->getData();
        $fields = [];
        $addItems = TSession::getValue('addItems');
        $productName = $this->product->getById($data->product_id);

        $product_id = $productName->id;
        $name = $productName->name;
        $quantity = $data->quantity;
        $price = $data->price;

        $fields['product_id'] = $product_id;
        $fields['name'] = $name;
        $fields['quantity'] = $quantity;
        $fields['price'] = $price;
        $addItems[$product_id] = (object) $fields;

        TSession::setValue('addItems', $addItems);

        $this->adItemsTable($param);
    }

    public function adItemsTable($param = null)
    {
        $items = TSession::getValue('addItems');

        if ($items) {
            $this->datagrid->clear();

            $cont = 1;
            foreach ($items as $key => $item) {
                $rowItem = new StdClass;
                $rowItem->product_id = $item->product_id;
                $rowItem->name = $item->name;
                $rowItem->quantity = $item->quantity;
                $rowItem->price = $item->price;
                // $rowItem->total = $item->total;

                $action_del = new TAction(array($this, 'onRemoveItem'));
                $action_del->setParameter('key', $key);

                $button_del = new TButton('deleteItem'.$cont);
                $button_del->setAction($action_del, 'delete');
                $button_del->setFormName($this->form->getName());

                $rowItem->delete = $button_del;

                $this->datagrid->addItem($rowItem);

                $cont++;
            }
        }
    }

    public function onRemoveItem($param = null)
    {
        $items = TSession::getValue('addItems');

        unset($items[$param['key']]);
        TSession::setValue('addItems', $items);

        $this->adItemsTable($param);
    }

    public function onCleanItems()
    {
        TSession::setValue('addItems', null);
    }

    public function onSave()
    {
        $finalTotal = 0;
        $object = new stdClass();
        $items = TSession::getValue('addItems');
        $quantity = array_column($items, 'quantity');
        $price = array_column($items, 'price');
        
        for ($i = 0; $i < sizeof($quantity); $i++) {
            $finalTotal += $quantity[$i] * $price[$i];
        }

        $object->totalFinal = $finalTotal;

        $this->sale->saveSale($object);
        $this->itemSale->saveItemsSale($this->sale->lastId(), $items);
        
        $this->onCleanItems();
    }

}