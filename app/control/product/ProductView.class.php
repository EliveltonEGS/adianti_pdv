<?php

use Adianti\Control\TPage;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Wrapper\TDBCombo;

class ProductView extends TPage
{
    private $form;
    private $datagrid;

    private $product;

    public function __construct()
    {
        parent::__construct();
        $this->product = new Product();

        $panel = new TPanelGroup('Products');

        //start form
        $this->form = new BootstrapFormBuilder();
        $this->form->setClientValidation(true);

        $id = new TEntry('id');
        $name = new TEntry('name');
        $quantity = new TEntry('quantity');;
        $price = new TEntry('price');
        $category_id = new TDBCombo('category_id', 'sample', 'Category', 'id', 'name', 'name asc');

        $id->setEditable(false);
        $name->disableAutoComplete();
        $quantity->disableAutoComplete();
        $price->disableAutoComplete();
        $category_id->enableSearch();

        $this->form->addFields([new TLabel('Id')], [$id], [new TLabel('Name')], [$name]);
        $this->form->addFields([new TLabel('Quantity')], [$quantity], [new TLabel('Price')], [$price]);

        $this->form->addFields([new TLabel('Category')], [$category_id]);

        $this->form->addAction('Save', new TAction([$this, 'onSave']), '');
        $this->form->addAction('Cancel', new TAction([$this, 'onCancel']), '');
        $this->form->addAction('List', new TAction([$this, 'onReload']), '');

        $panel->add($this->form);
        //end form

        //start line division
        $label = new TLabel('All Products', '#7D78B6', 12, 'bi');
        $label->style='color: #000;text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $panel->add($label);
        //end line division

        //start datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->width = '100%';

        $id = new TDataGridColumn('id', 'ID', 'center', '10%');
        $name = new TDataGridColumn('name', 'Name', 'left', '40%');
        $quantity = new TDataGridColumn('quantity', 'Quantity', 'center', '10%');
        $price = new TDataGridColumn('price', 'Price', 'center', '10%');
        $category_id = new TDataGridColumn('category_id', 'Category', 'center', '40%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($name);
        $this->datagrid->addColumn($quantity);
        $this->datagrid->addColumn($price);
        $this->datagrid->addColumn($category_id);

        $actionEdit = new TDataGridAction([$this, 'onEdit'], ['key' => '{id}']);
        $actionEdit->setUseButton(true);

        $actionDelete = new TDataGridAction([$this, 'onDelete'], ['key' => '{id}']);
        $actionDelete->setUseButton(true);

        $this->datagrid->addAction($actionEdit, 'Edit', 'fa:edit');
        $this->datagrid->addAction($actionDelete, 'Delete', 'fa:trash');

        $this->datagrid->createModel();       
        $panel->add($this->datagrid) ;
        //end datagrid

        $container = new TVBox();
        $container->style = 'width: 100%';
        $container->add($panel);

        parent::add($container);
    }

    public function onSave()
    {
        $data = $this->form->getData();

        if ($data->id == null) {
            $this->product->saveItem($data);
        } else {
            $this->product->updateItem($data);
        }
        
        $this->message('info', 'Sucessfully');
    }

    public function onReload()
    {
        $this->datagrid->clear();

        foreach ($this->product->getAllItems() as $item) {
            $object = new stdClass();
            $object->id = $item->id;
            $object->name = $item->name;
            $object->quantity = $item->quantity;
            $object->price = $item->price;
            $object->category_id = $item->category->name;
            $this->datagrid->addItem($object);
        }
        
    }

    public function onCancel()
    {
        $this->form->clear();
    }

    public function onEdit($param = null)
    {
        $data = $this->product->getById($param['key']);
        $this->form->setData($data);
    }

    public function onDelete($param = null)
    {
        $action = new TAction(array(__CLASS__, 'delete'));
        $action->setParameters($param);
        $this->onReload();
        new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }

    public function delete($param = null)
    {
        $this->product->deleteById($param['key']);
        $posAction = new TAction([__CLASS__, 'onReload']);
        new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $posAction);
    }

    private function message(string $type, string $message)
    {
        new TMessage($type, $message);
    }
}