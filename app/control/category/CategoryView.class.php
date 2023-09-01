<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class CategoryView extends TPage
{
    private $form;
    private $datagrid;

    private $category;

    public function __construct()
    {
        parent::__construct();
        $this->category = new Category();

        $panel = new TPanelGroup('Categries');

        //start form
        $this->form = new BootstrapFormBuilder();
        $this->form->setClientValidation(true);

        $id = new TEntry('id');
        $name = new TEntry('name');

        $id->setEditable(false);
        $name->disableAutoComplete();

        $this->form->addFields([new TLabel('Id')], [$id], [new TLabel('Name')], [$name]);

        $this->form->addAction('Save', new TAction([$this, 'onSave']), '');
        $this->form->addAction('Cancel', new TAction([$this, 'onCancel']), '');
        $this->form->addAction('List', new TAction([$this, 'onReload']), '');

        $panel->add($this->form);
        //end form

        //start line division
        $label = new TLabel('All Categories', '#7D78B6', 12, 'bi');
        $label->style='color: #000;text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $panel->add($label);
        //end line division

        //start datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->width = '100%';

        $id = new TDataGridColumn('id', 'ID', 'center', '10%');
        $name = new TDataGridColumn('name', 'Name', 'left', '90%');

        $this->datagrid->addColumn($id);
        $this->datagrid->addColumn($name);

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
            $this->category->saveItem($data);
        } else {
            $this->category->updateItem($data);
        }
        
        $this->message('info', 'Sucessfully');
    }

    public function onReload()
    {
        $this->datagrid->clear();

        $this->datagrid->addItems($this->category->getAllItems());
    }

    public function onCancel()
    {
        $this->form->clear();
    }

    public function onEdit($param = null)
    {
        $data = $this->category->getById($param['key']);
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
        $this->category->deleteById($param['key']);
        $posAction = new TAction([__CLASS__, 'onReload']);
        new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'), $posAction);
    }

    private function message(string $type, string $message)
    {
        new TMessage($type, $message);
    }
}