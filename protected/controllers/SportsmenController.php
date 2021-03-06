<?php
YiiBase::import('application.controllers.ParticipantController');
YiiBase::import('posting.models.*');
Yii::app()->bootstrap->register();

class SportsmenController extends ParticipantController
{
    
    public $layout='//layouts/column2';
    
    /**
    * Внешние экшены
    * 
    */
    public function actions()
    {
        // return external action classes, e.g.:
        return array(
            //// альтерн. вариант загрузки фоток
            //'saveImageAttachment' => 'ext.imageAttachment.ImageAttachmentAction',
        );
    }
    
    
    /**
    * Фильтры
    * 
    */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            //'postOnly + delete', // we only allow deletion via POST request
            array(
                'application.filters.UserFilter + create, update, delete',
                //'unit'=>'second', //здесб может быть параметр для фильтра
            ),  
            array(
                'application.filters.CompetitionFilter + create, update, delete',
            ),  
            array(
                'application.filters.SportsmenFilter + create',
            ),  
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index', 'view', /*'dynamicages',*/ 'dynamicweights', 'dynamiccoaches'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('create','update','delete','saveImageAttachment'),
                'users'=>array('@'),
                //'roles'=>array('admin','manager'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin', 'test'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    //ФУНКЦИЯ: загрузить модель СПОРТСМЕН
    public function loadModel($id)
    {
        $sportsmen = Sportsmen::model()->findByPk($id);
        if($sportsmen === null)
            throw new CHttpException(404, 'Запрашиваемая страница не существует! '.'Не найден спортсмен с идентификатором: '.$id);
        return $sportsmen;
    }
    
    //ФУНКЦИЯ: загрузить модель СПОРТСМЕН со всеми подчинёнными моделями (таблицами)
    public function loadModelAll($id)
    {
        $sportsmen = Sportsmen::model()->with('relCommand', 'relPhoto', 'relFst', 'relCategory', 'relAttestlevel', 
            'relCoachFirst', 'relCoach',
            'relAgecategory', 
            'relWeightcategory'
            //,'relWeightcategory.relAgecategory'
        )->findByPk($id);
        if($sportsmen === null)
            throw new CHttpException(404, 'Запрашиваемая страница не существует! '.'Не найден спортсмен с идентификатором: '.$id);
        return $sportsmen;
    }   
    
    //ДЕЙСТВИЕ: удаление спортсмена
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        $uid = Yii::app()->userid;
        $flg = Yii::app()->isExtendRole || $this->isUserOwner($uid, $model);
        if (!$flg) {
            if (Yii::app()->user->isGuest)
                $mess = 'Удаление запрещено! Вы вошли как Гость.'.
                        ' Для того, чтобы иметь возможность ввода информации, необходимо войти как зарегистрированный пользователь';
            else if (isset($uid) && !empty($uid))
                $mess = 'Удаление запрещено! Данный спортсмен введен другим пользователем! Удалять можно только своих спортсменов';
            throw new CHttpException(412, $mess);
            return;
        }
        $model->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('/command/index'));
    }    
    
    /**
    * ДЕЙСТВИЕ: смотреть данные о спорстмене
    * @param mixed $id
    */
    public function actionView($id) {
        $sportsmen = $this->loadModelAll($id);
        $command = $sportsmen->relCommand;
        $dataChangeLog = $sportsmen->getChangeLog();
        $this->render('view',array(
            'model'=>$sportsmen,
            'dataChangeLog'=>$dataChangeLog,
            'crumbs'=>array(
                'Команды'=>array('command/index'),
                $command->CommandName=>array('command/view', 'id'=>$command->CommandID),
                $sportsmen->FullName()
            )
        ));
    }
    
    //ДЕЙСТВИЕ: сформировать список спортсменов
    public function actionIndex() {   
        $count = Sportsmen::getSportsmenCount(); //Yii::app()->db->createCommand('SELECT COUNT(*) FROM Sportsmen')->queryScalar();
        $sqlCommand = Sportsmen::sqlSportsmenList();
        $dataProvider = new CSqlDataProvider($sqlCommand->text, array(
            'keyField'=>'SpID',
            'totalItemCount'=>$count,
            /*'sort'=>array(
                'attributes'=>array(
                    'Fullname',
                ),
            ),*/
            'pagination'=>array(
                'pageSize'=>50,
            ),
        ));    
        $this->render('index',array(
            'commandid'=>null,
            'dataProvider'=>$dataProvider,
        ));
    }    
    
    // РЕДАКТИРОВАТЬ спортсмена ---------------------------------------------------------------------
    public function actionUpdate($id)
    {
        $urlRequest = Yii::app()->request->hostInfo.Yii::app()->request->url; //полная ссылка текущего запроса
        
        $urlReferrer = Yii::app()->user->getState('urlReferrerUpdate');  //прочитать пред.ссылку из сессии
        if (empty($urlReferrer)) {                                       // и если таковой нету -
            $urlReferrer = Yii::app()->request->urlReferrer;  //получить - полная ссылка предыд. страницы
            Yii::app()->user->setState('urlReferrerUpdate', $urlReferrer);  //и записать в сессию
        }
        
        $competition = Competition::getModel();

        $model = Sportsmen::model()->with('relCommand')->findByPk($id);
        $uid = Yii::app()->userid;
        $flg = Yii::app()->isExtendRole || $this->isUserOwner($uid, $model);
        if (!$flg) {
            if (isset($uid) && !empty($uid))
                $mess = 'Запрещено редактировать! Данный спортсмен введен другим пользователем';
            throw new CHttpException(401, $mess);
            return;
        }
        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);
        if(isset($_POST['Sportsmen']))
        {
            if ($competition->isCompetition) {
                $model->scenario = 'wtf';
            } else if ($competition->type == 'itf') {
                $model->scenario = 'itf';
            }

            $flgDelPhoto = false;
            $model->attributes = $_POST['Sportsmen'];
            
            // --- Установить фото спортсмена / заменить / удалить
            // вначале определяем - удалять ли старый фото и новый ИД фото 
            $newPhotoId = isset($_POST['PostFiles']) ? array_shift($_POST['PostFiles']) : false;
            $photoToDelete = (isset($model->relPhoto) && (($newPhotoId == false) || ($newPhotoId <> $model->relPhoto->photo_id))) ? $model->relPhoto : null;
            // затем ставим новый ИД фото, если есть новое фото, или обнуляем, если фото удалено
            if ($newPhotoId) {
                $model->photoid = $newPhotoId;
            } else if ($photoToDelete) {
                $model->photoid = null;
            }
            
            // Проверить модель и выполнить обработку
            if ($model->validate()) {
                //стереть сессию урла возврата
                Yii::app()->user->setState('urlReferrerUpdate', null);
                //стартовать транзакцию и сохранить инфо
                $transaction = Yii::app()->db->beginTransaction();                         
                try {
                    if ($model->save()) { //успешное сохранение
                        // проверить удаленять ли фото
                        if ($photoToDelete) {
                            $photoToDelete->delete(); //удалить объект + файл картинки
                        }
                    } else {
                        throw new Exception(sprintf('Ошибка при сохранении спортсмена %d!', $model->SpID));  
                    }
                    $transaction->commit();            
                    $success = true;
                } catch (Exception $e){
                    $transaction->rollBack();
                    $success = false;
                    $error = $e->getMessage();
                }       
                
                if ($success) { //успешное выполнение  
                    if (!empty($urlReferrer) && ($urlRequest <> $urlReferrer)) {
                        $this->redirect($urlReferrer);  //переход по запомненной
                    } else                              //иначе - на вью спортсмена
                        $this->redirect(array('view','id'=>$model->SpID));
                } else {
                    throw new CHttpException(401, $error);
                }
            }
        }

        $command = $model->relCommand;
        
        $breadcrumbs = array('Команды'=>array('command/index'));
        if (isset($command))
            $breadcrumbs = array_merge($breadcrumbs, array($command->CommandName=>array('command/view', 'id'=>$command->CommandID)));
        $breadcrumbs = array_merge($breadcrumbs, array($model->FullName()=>array('sportsmen/view', 'id'=>$model->SpID), Yii::t('controls', 'Update')));
        
        if ($competition->isCompetition)
            $years = Agecategory::getYears();
        else 
            $years = Agecategory::getFullYears();
        
        $this->render('update',array(
            'model'=>$model,
            'extendRole'=>Yii::app()->isExtendRole,
            'years'=>$years,
            'ages'=>Agecategory::getAges(), //выборка категорий соревнования (возрастные с весовыми)
            'crumbs'=>$breadcrumbs
        ));
    }    
    
    
  //СОЗДАТЬ спортсмена ---------------------------------------------------------------------
  //$id - ИД команды, куда будет добавляться спортсмен
  // перед действием будут выполнены проверки (см. ФИЛЬТРЫ для контроллера)  
    public function actionCreate($id = null) {
        // $this->performAjaxValidation($model); // Uncomment the following line if AJAX validation is needed
        // для проверки: выбираем команду и связанную с ней заявку
        $command = Command::model()->with('relProposal')->findByPk($id);
        if (!isset($command)) {
            throw new CHttpException(404, 'Команда с таким ID не существует!');
        } else if (!isset($command->relProposal)) {
            throw new CHttpException(410, 'Ошибка в структуре данных команд! Обратитесь к администратору');
        }
      //РАЗЛИЧНЫЕ ПРОВЕРКИ  
        if (!Yii::app()->isExtendRole) {
            $status = $command->relProposal->status;
            if (!isset($status)) {
                throw new CHttpException(401, "Запрещено добавление спортсменов! Вначале необходимо подать заявку на соревнование!\r\n".
                    'Для разрешения проблемы свяжитесь с организаторами соревнований');
            }
            if ($status <> Proposal::STATUS_ACTIVE) {
                throw new CHttpException(400, "Запрещено добавление спортсменов! Ваша заявка не подтверждена!"."\n\r".
                    "Для разрешения проблемы свяжитесь с организаторами соревнований");
            } else {
                // проверка "моя ли это команда"?
                $isMyCommand = Yii::app()->user->isMyCommand($id);
                if (!Yii::app()->isExtendRole && !$isMyCommand)
                    throw new CHttpException(400, 'Запрещено добавлять спортсменов в чужой команде! Для ввода данных выберите свою команду');
            }
        } 
        
        $competition = Competition::getModel();

      //все проверки пройдены - создаём модель  
        $model = new Sportsmen;
        $model->CommandID = $id;  //присвоить модели ИД команды

        //если пришли данные из формы
        if (isset($_POST['Sportsmen']))        
        {
            if ($competition->isCompetition) {
                $model->scenario = 'wtf';
            } else if ($competition->type == 'itf') {
                $model->scenario = 'itf';
            }
            $model->attributes = $_POST['Sportsmen'];
            $model->UserID = Yii::app()->userid;
            // обработать фото - если было загружено в форму
            if (isset($_POST['PostFiles'])) {
               foreach($_POST['PostFiles'] as $varName=>$value) { 
                  if (strpos($varName,'photoIds') !== false) {
                        $model->photoid = $value;
                  }
               }
            } 
            
            if($model->save()) {
                //$this->redirect(array('/command/view','id'=>$id, 'tab'=>'1'));
                //$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('/command/index'));
                Yii::app()->user->setFlash('success', 'Новый спортсмен успешно добавлен: ' . $model->LastName . ' ' . $model->FirstName);
                if (isset($_POST['save_new'])) {  //если была нажата кнопка "добавить и нового"
                    $this->redirect(array('/sportsmen/create', 'id'=>$id));
                } else if (isset($_POST['save_exit'])) { //если была нажата кнопка "добавить и вернуться"
                    $this->redirect(array('command/view', 'id'=>$command->CommandID));
                }
            }
        }
        
        $breadcrumbs = array('Команды'=>array('command/index'));
        if (isset($command))
            $breadcrumbs = array_merge($breadcrumbs, array($command->CommandName=>array('command/view', 'id'=>$command->CommandID)));
        $breadcrumbs = array_merge($breadcrumbs, array(Yii::t('controls', 'Create')));
        
        if ($competition->isCompetition)
            $years = Agecategory::getYears();
        else 
            $years = Agecategory::getFullYears();
        
        $this->render('create',array(
            'model'=>$model,
            'years'=>$years,
            'ages'=>Agecategory::getAges(), //выборка категорий соревнования (возрастные с весовыми)
            'extendRole'=>Yii::app()->isExtendRole,
            'crumbs'=>$breadcrumbs,
        ));
    }

    //ДЕЙСТВИЕ: админ
    public function actionAdmin() {
        $model=new Sportsmen('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['Sportsmen']))
            $model->attributes=$_GET['Sportsmen'];

        $this->render('admin',array(
            'model'=>$model,
            'extendRole'=>Yii::app()->isExtendRole,
        ));
    }

    
    
    private function getSpIDfromForm() {
        if ($_POST['Sportsmen']['SpID'] <> "") {
            $id = (int)$_POST['Sportsmen']['SpID'];
            return $id;
        }
    }
    
    //динамически: подгрузить список возрастов в зависимости от пола
    /*public function actionDynamicages() {
        $gender = $_POST['Sportsmen']['Gender'];
        //$data = Sportsmen::getAgesList($gender);   //получить список моделей возрастов по полу
        $data = Agecategory::getAges();   //получить список моделей возрастов по полу
        $data = CHtml::listData($data, 'AgeID', 'AgeNameYear');  //перевести в список
        echo CHtml::tag('option', array('value' => ''), '<Выберите возрастную категорию>', true);
        //if ($_POST['Sportsmen']['SpID'] <> "") {
//            $id = (int)$_POST['Sportsmen']['SpID'];
//            $id = $this->loadModel($id)->AgeID;       //Текущий ИД возраста
//        }
        foreach($data as $value => $name) {
            $options = array('value' => $value);
            //if (isset($id) && $value == $id)          //поставить запомненный ИД возраста 
            //    $options['selected'] = 'selected';    //как выбранный
            echo CHtml::tag('option', $options, CHtml::encode($name), true);
        }
    }*/

    //динамически: подгрузить список весов в зависимости от возраста
    public function actionDynamicweights() {
        $age = (int)$_POST['Sportsmen']['AgeID'];
        $data = Sportsmen::getWeigthsList($age);   //получить список моделей весов по возрасту
        $data = CHtml::listData($data, 'WeightID', 'WeightNameFull');
        echo CHtml::tag('option', array('value' => ''), '<Выберите весовую категорию>', true);
          
        /*if ($_POST['Sportsmen']['SpID'] <> "") {
            $id = (int)$_POST['Sportsmen']['SpID'];
            $id = $this->loadModel($id)->WeigthID;
        }*/
        foreach($data as $value => $name) {
            $options = array('value' => $value);
            //if (isset($id) && $value == $id)
            //    $options['selected'] = 'selected';
            echo CHtml::tag('option', $options, CHtml::encode($name), true);
        }
    }

    //динамически: подгрузить список тренеров в зависимости от команды
    public function actionDynamiccoaches() {
        $command = $_POST['Sportsmen']['CommandID'];
        $data = Sportsmen::getCoachList($command);   //получить список моделей тренеров по команде
        $data = CHtml::listData($data, 'CoachID', 'CoachName');
        echo CHtml::tag('option', array('value' => ''), '<Выберите тренера>', true);
          
        /*if ($_POST['Sportsmen']['SpID'] <> "") {
            $id = (int)$_POST['Sportsmen']['SpID'];
            $id = $this->loadModel($id)->AgeID;
        }*/
        foreach($data as $value => $name) {
            $options = array('value' => $value);
            //if (isset($id) && $value == $id)
            //    $options['selected'] = 'selected';
            echo CHtml::tag('option', $options, CHtml::encode($name), true);
        }
    }

    
    /**
    * put your comment there...
    * 
    */
    public function actionTest() {
        $this->render('test');
    }
}
