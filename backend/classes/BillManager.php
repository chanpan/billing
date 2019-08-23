<?phpnamespace backend\classes;use appxq\sdii\utils\VarDumper;use backend\models\BillItems;use backend\models\BillType;use backend\models\SellBill;use backend\models\SellItems;use Yii;use yii\db\Exception;class BillManager{    public static  function renderBillDetail(){        $billId = isset(Yii::$app->session['bill_id'])?Yii::$app->session['bill_id']:'';        $bill = BillItems::find()->where('id=:id and rstat not in(0,3)',[            ':id'=>$billId        ])->one();       if($bill){           $billType = BillType::findOne($bill->bill_type);           $billStatus = BillType::findOne($bill->billref);           $type = isset($billType->name)?$billType->name:'ไม่ได้ตั้ง';           $status = isset($billStatus->name)?$billStatus->name:'ไม่ได้ตั้ง';           $price = number_format($bill->amount, 2);           return '<div class="kt-portlet kt-iconbox kt-iconbox--success kt-iconbox--animate-slow">'           . '<div class="kt-portlet__body"><div class="kt-iconbox__body">'                   . '<div class="kt-iconbox__icon"><br></div><div class="kt-iconbox__desc">'                   . '<h4 class="kt-iconbox__title" style="font-size: 1.6rem;"><a class="kt-link" href="#">'                   . '<img src="https://img.icons8.com/color/48/000000/check.png"></a>'                   . 'หมายเลขบิล:'.$bill->billno.' เลขที่เอกสาร: '.$bill->billref.' ประเภทบิล: '.$type.' สถานะบิล: '.$status.' บิลเล่มที่: '.$bill->bookno.' จำนวนเงิน: '.$price.' บาท</h4>'                   . '</div></div></div></div>';           return "                           <div class='alert alert-info'>                <div>                    <img src=\"https://img.icons8.com/color/48/000000/check.png\">                    หมายเลขบิล:{$bill->billno} เลขที่เอกสาร:<b>{$bill->billref}</b> ประเภทบิล: {$type} สถานะบิล:{$status} บิลเล่มที่:{$bill->bookno}                &nbsp;&nbsp;&nbsp;&nbsp; <label>จำนวนเงิน: {$price} บาท</label>                </div>            </div>           ";       }    }    /**     * @param $fileName     * @throws \PHPExcel_Exception     * @throws \PHPExcel_Reader_Exception     */    public static function saveData($fileName){        ini_set('memory_limit', '-1');        set_time_limit(500); //        $path = \Yii::getAlias('@storage').'/web/uploads';        $file= "{$path}/{$fileName}";        try{            $inputFile = \PHPExcel_IOFactory::identify($file);            $objReader = \PHPExcel_IOFactory::createReader($inputFile);            $objPHPExcel = $objReader->load($file);        }catch (Exception $e){            return [                'status'=>false,                'message'=>'เกิดข้อผิดพลาด'. $e->getMessage()            ];                     }        $sheet = $objPHPExcel->getSheet(0);        $highestRow = $sheet->getHighestRow();        $highestColumn = $sheet->getHighestColumn();        $objWorksheet = $objPHPExcel->getActiveSheet();        $arr=[];        foreach($objWorksheet->getRowIterator() as $rowIndex => $row){            if($rowIndex < 10){ continue; }            $arr[] = $objWorksheet->rangeToArray('A'.$rowIndex.':'.$highestColumn.$rowIndex, null, false);        }        $column = [        ];        $docno = '';        foreach($arr as $k=>$v){            $date = isset($v[0][1]) ? $v[0][1]:'';            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))            {                try{                    unset($v[0][0]);                    $docno = isset($v[0][2])?$v[0][2]:'';                    $shellBill = SellBill::find()->where('docno=:docno',[                        ':docno' => $docno                    ])->one();                    if(!$shellBill){ $shellBill = new SellBill(); }                    $shellBill->docno = $docno;                    $shellBill->docdate = $date;                    $shellBill->doctime = isset($v[0][3])?$v[0][3]:null;                    $shellBill->refdata = isset($v[0][4])?$v[0][4]:null;                    $shellBill->refdate = isset($v[0][5])?$v[0][5]:null;                    $shellBill->customerno = isset($v[0][6])?$v[0][6]:null;                    $shellBill->customername = isset($v[0][7])?$v[0][7]:null;                    $shellBill->totalprice = isset($v[0][8])?(string)$v[0][8]:null;                    $shellBill->netprice = isset($v[0][10])?(string)$v[0][10]:null;                    if(!$shellBill->save()){VarDumper::dump($shellBill->errors);}                }catch (Exception $ex){}            }else{                try{                    $itemcode = isset($v[0][1])?$v[0][1]:null;                    if($itemcode == null) {continue;}                    $sellItems = SellItems::find()->where('docno=:docno AND itemcode=:itemcode',[                        ':docno'=>$docno,                        ':itemcode'=>$itemcode                    ])->one();                    if(!$sellItems){ $sellItems = new SellItems(); }                    $sellItems->docno = $docno;                    $sellItems->itemcode = $itemcode;                    $sellItems->itemname = isset($v[0][2])?$v[0][2]:null;                    $sellItems->treasury = isset($v[0][3])?$v[0][3]:null;                    $sellItems->storage = isset($v[0][4])?$v[0][4]:null;                    $sellItems->unit = isset($v[0][5])?$v[0][5]:null;                    $sellItems->amount = isset($v[0][6])?(string)$v[0][6]:null;                    $sellItems->unitprice = isset($v[0][7])?(string)$v[0][7]:null;                    $sellItems->unitdiscount = isset($v[0][8])?(string)$v[0][8]:null;                    $sellItems->discountvalue = isset($v[0][9])?$v[0][9]:null;                    $sellItems->totaldiscount = isset($v[0][10])?(string)$v[0][10]:null;                    if(!$sellItems->save()){ VarDumper::dump($sellItems->errors); }                }catch (Exception $ex){}            }        }        return [                'status'=>true,                'message'=>'เพิ่มข้อมูลสำเร็จ'        ];    }}