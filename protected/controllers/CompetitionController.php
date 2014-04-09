<?php

class CompetitionController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('view', 'invite', 'archive'/*, 'tosser', 'results'*/),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('update', 'manage', 'exportcsv'),
                'roles'=>array('admin','manager'),
				//'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
   
   private function getCompetitionStat() {
        $sqlCommand = Competition::sqlStat();
        $dataProvider = new CSqlDataProvider($sqlCommand->text, array(  
            'params'=>array(
                ':competitionid'=>Yii::app()->competitionId, 
                ':status'=>Proposal::STATUS_ACTIVE,
            ),
            'totalItemCount'=>3,
            'keyField'=>'statname',
        ));
        return $dataProvider;
   }

	//ДЕЙСТВИЕ: плакат-приглашение
	public function actionInvite()
	{
        $path = Yii::app()->request->getParam('path');
        $id = Yii::app()->request->getParam('id');
        
        $this->pathCompetition = isset($path) ? $path : '';
        
        $model = !empty($path) ? Competition::getModelPath($path) : Competition::getModel($id);
        $this->render('invitation',array(
            'model'=>$model,
            //'dataStat'=>$this->getCompetitionStat(), //$dataProvider,
		));
	}

    //ДЕЙСТВИЕ: просмотр
    public function actionView()
    {
        $this->render('view',array(
            'model'=>Competition::getModel(),
            'dataStat'=>$this->getCompetitionStat(), //$dataProvider,
        ));
    }

    //ДЕЙСТВИЕ: управление
    public function actionManage()
    {
        $this->render('manage',array(
            'model'=>Competition::getModel(),
            'dataStat'=>$this->getCompetitionStat(), //$dataProvider,
        ));
    }

    public function actionExportcsv()
    {//DebugBreak();
        Yii::import('ext.csv.ECSVExport');
        $outputFile = 'participants.csv';
        $cmd = Yii::app()->db->createCommand("SELECT * FROM fulllist WHERE competitionid = " . Yii::app()->competitionId);
        $csv = new ECSVExport($cmd);
        $csv->delimiter = ';';
        $csv->setOutputFile($outputFile);
        $csv->toCSV(); // returns string by default
         
        //echo file_get_contents($outputFile);
        $content = file_get_contents($outputFile);
        Yii::app()->getRequest()->sendFile($outputFile, $content, "text/csv", false);
    }
    
	//ДЕЙСТВИЕ: редактирование
	public function actionUpdate($id = null) {
		if ($id === null) {
            $id = Yii::app()->competitionId;
        }
        $model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Competition'])) {
			$model->attributes=$_POST['Competition'];
            if ($model->isInviteChanged) { //если было изменение главной страницы - то сохраняем его
                $model->invitation = $_POST['Competition']['invitation'];
            }
			if($model->save()) {
				$this->redirect(array('manage','id'=>$model->id));
            }
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	//функция: загрузить модель
	public function loadModel($id = 0)
	{
		$model=Competition::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'Запрашиваемая страница не найдена. Сообщите об ошибке организаторам соревнований');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='competition-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}  
    
    /**
    * Вывод архива соревнований (временно): результаты
    * 
    * @param mixed $id
    */
    public function actionArchive() {
        $this->render('archive');
    }
}
