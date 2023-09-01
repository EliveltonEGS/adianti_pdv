<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;

class ReportView extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();
        $this->form = new BootstrapFormBuilder('report_view');

        $price = new TLabel('Sample Report of Sales');

        $this->form->addFields([$price]);

        $this->form->addAction('Generate', new TAction([$this, 'onGenerate']), 'fa:download blue');

        $container = new TVBox();
        $container->style = 'width: 100%';
        $container->add($this->form);

        parent::add($container);
    }

    public function onGenerate()
    {
        TTransaction::open('sample');

        $sales = Sale::all();
        
        $widths = array(40, 200, 120, 80, 80, 80);
        $table = new TTableWriterPDF($widths);

        $table->addStyle('header', 'Helvetica', '16', 'B', '#ffffff', '#4B5D8E');
        $table->addStyle('title',  'Helvetica', '10', 'B', '#ffffff', '#617FC3');
        $table->addStyle('datap',  'Helvetica', '10', '',  '#000000', '#E3E3E3', 'LR');
        $table->addStyle('datai',  'Helvetica', '10', '',  '#000000', '#ffffff', 'LR');
        $table->addStyle('footer', 'Helvetica', '10', '',  '#2B2B2B', '#B4CAFF');
        
        $table->setHeaderCallback( function($table) {
            $table->addRow();
            $table->addCell('All sales', 'center', 'header', 6);
            
            $table->addRow();
            $table->addCell('Id',      'center', 'title');
            $table->addCell('Total',      'left',   'title');
            $table->addCell('created_at',     'left',   'title');
        });
        
        $table->setFooterCallback( function($table) {
            $table->addRow();
            $table->addCell(date('Y-m-d h:i:s'), 'center', 'footer', 6);
        });

        $colour= false;
        foreach ($sales as $sale) {
            $style = $colour ? 'datap' : 'datai';

            $table->addRow();
            $table->addCell($sale->id, 'center', $style);
            $table->addCell($sale->total, 'center', $style);
            $table->addCell($sale->created_at, 'center', $style);

            $colour = !$colour;
        }

        TTransaction::close();

        $output = "app/output/tabular.pdf";
        $table->save($output);
        parent::openFile($output);
    }
}