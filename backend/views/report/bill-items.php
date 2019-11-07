<?= $this->render('_search')?><div style="background: #fff;padding: 10px;"><?php    echo \kartik\grid\GridView::widget([        'dataProvider' => $dataProvider,        'columns' => [            [                'headerOptions' => ['style'=>'width:50px;text-align:center;'],                'class' => 'yii\grid\SerialColumn',                // you may configure additional properties here            ],            [                'format' => 'raw',                'attribute' => 'docno',                'value' => function ($model) {                    //billref                    if(isset($model->billtypes->name)){                        $docno =  "{$model->billtypes->name}{$model->billref}";                        $dataSellBill = \backend\classes\BillManager::checkSellBill($docno);                        if(isset($dataSellBill)){                            if($model->amount != $dataSellBill['net_value']){                                return "                                        <div style='padding:5px;' class='kt-border-warning kt-section section-sm kt-portlet__body body-xs kt-font-warning content-border'>                                            <div class='row'>                                                <div class='col-md-8 col-xs-8 col-sm-8'>{$docno} <label class='label label-warning'>จำนวนเงินไม่ตรงกัน</label></div>                                                <div class='col-md-4 col-xs-4 col-sm-4 text-right'><button class='btn btn-info btnEdit' data-id='{$model->id}'><i class='fa fa-pencil'></i> แก้ไขบิล</button></div>                                            </div>                                        </div>                                    ";                            }                            return $docno;                        }else{                            return "                                        <div style='padding:5px;' class='kt-border-danger kt-section section-sm kt-portlet__body body-xs kt-font-danger content-border'>                                            <div class='row'>                                                <div class='col-md-8 col-xs-8 col-sm-8'>{$docno}</div>                                                <div class='col-md-4 col-xs-4 col-sm-4 text-right'><button data-id='{$model->id}' class='btn btn-success btnUpload'><i class='fa fa-plus'></i> อัปโหลดบิล</button>                                                  <button class='btn btn-info btnEdit' data-id='{$model->id}'><i class='fa fa-pencil'></i> แก้ไขบิล</button>                                                </div>                                            </div>                                        </div>                                    ";                        }                        //\appxq\sdii\utils\VarDumper::dump($dataSellBill);                    }                }            ],            [                'format' => 'raw',                'contentOptions' => ['style' => 'width:100px'],                'attribute'=>'amount',                'value'=>function($model){                    return $model->amount;                }            ],            [                'format' => 'raw',                'contentOptions' => ['style' => 'width:100px'],                'label'=>'จำนวนเงินจากไฟล์รายงาน',                'value'=>function($model){                    if(isset($model->billtypes->name)){                        $docno =  "{$model->billtypes->name}{$model->billref}";                        $dataSellBill = \backend\classes\BillManager::checkSellBill($docno);                        if($dataSellBill){                            return $dataSellBill['net_value'];                        }                    }                }            ],        ]    ]);use appxq\sdii\widgets\ModalForm; ?></div><?=ModalForm::widget([    'id' => 'modal-bill-items',    'options' => ['tabindex' => false],    'size' => 'modal-lg',    //         'clientOptions' => ['backdrop' => 'static', 'keyboard' => false]]);?><?php \richardfan\widget\JSRegister::begin(); ?><script>    $(".btnEdit").on('click', function(){        let id = $(this).attr('data-id');        let url = '<?= \yii\helpers\Url::to(['/bill-items/update?id='])?>'+id;        modalBillItem(url);        return false;    });    $(".btnUpload").on('click', function(){        let id = $(this).attr('data-id');        let url = '<?= \yii\helpers\Url::to(['/bill-items/bill-upload'])?>';        window.open(url,'_blank');        return false;    });    $(".btnViewProduct").on('click', function(){        let id = $(this).attr('data-docno');        let url = '<?= \yii\helpers\Url::to(['/report/sell-product-by-docno?docno='])?>'+id;        modalBillItem(url);        return false;    });    function modalBillItem(url) {        $('.modal').css('overflow', 'scroll');        $('#modal-bill-items .modal-content').html('<div class=\"sdloader \"><i class=\"sdloader-icon\"></i></div>');        $('#modal-bill-items').modal('show')            .find('.modal-content')            .load(url);    }    $("#btnProcess").on('click', function () {        let stdate = $("#stdate").val();        let endate = $("#endate").val();        let url = '<?= \yii\helpers\Url::to(['/report/sell-bill'])?>';        url = `${url}?stdate=${stdate}&endate=${endate}`;        location.href=url;        return false;    });</script><?php \richardfan\widget\JSRegister::end(); ?>