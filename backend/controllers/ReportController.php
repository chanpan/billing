<?phpnamespace backend\controllers;use appxq\sdii\utils\SDdate;use appxq\sdii\utils\SDUtility;use appxq\sdii\utils\VarDumper;use backend\classes\BillManager;use backend\models\BillItems;use backend\models\BillType;use backend\models\Commissions;use backend\models\Groups;use backend\models\SellBill;use backend\models\SellItems;use backend\models\UserSippings;use yii\data\ActiveDataProvider;use yii\db\Expression;use yii\db\Query;use yii\web\Controller;class ReportController extends Controller{    public function beforeAction($action)    {        $actionArr = ['customer-car','sell-bill','bill-items','find-bill-all','customer-car-data','sell-product-by-docno','block','block-data'];        if(in_array($action->id , $actionArr))        {            if(!\Yii::$app->user->can('report')){                return $this->redirect(['/site/']);            }        }        //return true;        return parent::beforeAction($action);    }    public function actionCustomerCar()    {        return $this->render("customer-car");    }    public function actionIndex(){        $stdate = \Yii::$app->request->get('stdate');        $endate = \Yii::$app->request->get('endate');        $bill_type = \Yii::$app->request->get('bill_type');        $rstat = \Yii::$app->request->get('rstat');        $bill_status = \Yii::$app->request->get('bill_status');        //        $model = BillItems::find()->where('rstat not in(3)');        if($stdate != null && $endate != null){            $stdate = SDdate::convertDmyToYmd($stdate);            $endate = SDdate::convertDmyToYmd($endate);            $model = $model->andWhere(['between', 'bill_date', $stdate, $endate]);        }        if($bill_type){            $model = $model->andWhere('bill_type=:bill_type',[                ':bill_type' => $bill_type            ]);        }        if($rstat){            $model = $model->andWhere('rstat=:rstat',[                ':rstat' => $rstat            ]);        }        if($bill_status){            $model = $model->andWhere('status=:status',[                ':status' => $bill_status            ]);        }        //status        $model = $model->orderBy(['id'=>SORT_DESC]);        $dataProvider = new ActiveDataProvider([            'query' => $model,            'pagination' => [                'pageSize' => 100,            ],        ]);        $count = $dataProvider->getModels();        return $this->render("index",[            'dataProvider'=>$dataProvider,            'count'=>count($count)        ]);    }    public function actionSellBill(){        $stdate = \Yii::$app->request->get('stdate');        $endate = \Yii::$app->request->get('endate');        $model = SellBill::find();        if($stdate != null && $endate != null){            $stdate = SDdate::convertDmyToYmd($stdate);            $endate = SDdate::convertDmyToYmd($endate);            //VarDumper::dump($stdate);            $model = $model->andWhere(['between', 'docdate', $stdate, $endate]);        }        $dataProvider = new ActiveDataProvider([            'query' => $model,            'pagination' => [                'pageSize' => 500,            ],        ]);        return $this->render("sell-bill",[            'dataProvider'=>$dataProvider        ]);    }    public function actionBillItems(){        $model = BillItems::find()            ->where('rstat not in(0,3) AND status <> 5');        $dataProvider = new ActiveDataProvider([            'query' => $model,            'pagination' => [                'pageSize' => 2000,            ],        ]);        return $this->render("bill-items",[            'dataProvider'=>$dataProvider        ]);    }    public function actionFindBillAll()    {        return $this->render("find-bill-all",[            //'dataProvider'=>$dataProvider        ]);    }    public function actionCustomerCarData()    {        $output = [];        $stdate = \Yii::$app->request->post('stdate');        $endate = \Yii::$app->request->post('endate');        $stdate = SDdate::convertDmyToYmd($stdate);        $endate = SDdate::convertDmyToYmd($endate);        $query = new Query();        $billAll = $query->select('*')            ->from('bill_items as bis')            ->andWhere(['between', 'bill_date', $stdate, $endate])            ->all();        if (!$billAll) {            return "<h1>ไม่พบข้อมูล</h1>";        }        $token = SDUtility::getMillisecTime();        foreach ($billAll as $k => $v) {            $bill = BillManager::reportCustomerCar($v['id']);            foreach($bill as $k2=>$b){                if(isset($b['user_id'])){                    $model = new Commissions();                    $model->token = $token;                    $model->bill_id = $v['id'];                    $model->user_id = $b['user_id'];                    $model->driver = $b['driver'];                    $model->position = $b['position'];                    $model->price = $b['price'];                    $model->create_date = $v['bill_date'];                    $model->save();                }            }            $data = Commissions::find()                ->where('token=:token',[':token' => $token])->all();            $output = $data;            foreach($data as $k=>$v){                $v->delete();            }            //VarDumper::dump($data);            return $this->renderAjax("customer-car-data",[                'data'=>$output            ]);        }    }    public function actionSellProductByDocno(){        $docno = \Yii::$app->request->get('docno');        $model = SellItems::find()->where('docno=:docno',[":docno" => $docno]);        $dataProvider = new ActiveDataProvider([            'query' => $model,            'pagination' => [                'pageSize' => 10000,            ],        ]);        return $this->renderAjax("sell-product-by-docno",[            'dataProvider'=>$dataProvider        ]);    }    public function actionBlock(){        $block=Groups::find()->all();        return $this->render("block",[            'block'=>$block        ]);    }    public function actionBlockData(){        if(\Yii::$app->request->post()){            $block = \Yii::$app->request->post('block');            \Yii::$app->session['block'] = $block;            $blog = Groups::findOne($block);            $billno = [];           //VarDumper::dump($blog);            for($i=$blog->value;$i<$blog->value+500; $i++){                array_push($billno,(string)$i);            }            //$model = BillItems::find()->where(['billno'=>$billno])->all();        }        return $this->renderAjax("block-data",[            'billno'=>$billno,            'block'=>$block,            'blog'=>$blog            //'model'=>$model        ]);    }    public function actionCustomerPackage(){        return $this->render("customer-package",[        ]);    }    public function actionCustomerPackageData()    {        $output = [];        $stdate = \Yii::$app->request->post('stdate');        $endate = \Yii::$app->request->post('endate');        $stdate = SDdate::convertDmyToYmd($stdate);        $endate = SDdate::convertDmyToYmd($endate);        $query = new Query();        $billAll = $query->select('*')            ->from('bill_items as bis')            ->andWhere(['between', 'bill_date', $stdate, $endate])            ->all();        if (!$billAll) {            return "<h1>ไม่พบข้อมูล</h1>";        }        $token = SDUtility::getMillisecTime();        foreach ($billAll as $k => $v) {            $bill = BillManager::reportCustomerCar($v['id']);            foreach($bill as $k2=>$b){                if(isset($b['user_id'])){                    $model = new Commissions();                    $model->token = $token;                    $model->bill_id = $v['id'];                    $model->user_id = $b['user_id'];                    $model->driver = $b['driver'];                    $model->position = $b['position'];                    $model->price = $b['price'];                    $model->create_date = $v['bill_date'];                    $model->save();                }            }            $data = Commissions::find()                ->where('token=:token',[':token' => $token])->all();            $output = $data;            foreach($data as $k=>$v){                $v->delete();            }            //VarDumper::dump($data);            return $this->renderAjax("customer-car-data",[                'data'=>$output            ]);        }    }}