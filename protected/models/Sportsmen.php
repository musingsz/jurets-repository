<?php

/**
 * This is the model class for table "sportsmen".
 *
 * The followings are the available columns in table 'sportsmen':
 * @property integer $SpID
 * @property string $LastName
 * @property string $FirstName
 * @property string $MiddleName
 * @property string $BirthDate
 * @property string $Gender
 * @property integer $CommandID
 * @property integer $FstID
 * @property integer $CategoryID
 * @property integer $AttestLevelID
 * @property integer $WeigthID
 * @property integer $Coach1ID
 * @property integer $Coach2ID
 * @property integer $MedicSolve
 * @property integer $AgeID
 * @property integer $UserID
 */
class Sportsmen extends CActiveRecord
{
    const STATUS_NOACTIVE = 0;
    const STATUS_ACTIVE = 1;
    //const STATUS_NEW = -1;

    public $searchAgeName;   //
    public $FullName;        //
    public $searchCoachName; //
    /*public $BirthYear;  //Jurets
    public $AgeName;  //Jurets
    public $WeightNameFull;  //Jurets
    public $Coachname;  //Jurets
	public $Coachname1;*/  //Jurets
    
    private $_weightlist = array();
    
    /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Sportsmen the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'sportsmen';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('LastName, FirstName, CommandID, Gender, AgeID, WeigthID', 'required'),
            array('AttestLevelID', 'required'), //
            array('CategoryID, BirthDate', 'required', 'on'=>'wtf'),
			array('CommandID, FstID, CategoryID, AttestLevelID, WeigthID, Coach1ID, Coach2ID, MedicSolve, AgeID', 'numerical', 'integerOnly'=>true),
			array('LastName', 'length', 'max'=>30),
			array('FirstName, MiddleName, IdentCode', 'length', 'max'=>20),
			array('Gender', 'length', 'max'=>1),
			//array('BirthDate', 'safe'),
            array('MedicSolve', 'default', 'value'=>1),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
            array('SpID, LastName, FirstName, MiddleName, BirthDate, Gender, CommandID, FstID, CategoryID, AttestLevelID, WeigthID, Coach1ID, Coach2ID, MedicSolve, AgeID, persontul', 'safe', 'on'=>'search'),
            array('fullyears, persontul', 'safe', 'on'=>'itf'),
            array('FullName, searchAgeName, searchCoachName', 'safe', 'on'=>'search'),
			//array('searchFullName', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
        return array(
            'Command'=> array(self::BELONGS_TO, 'Command', 'CommandID'),
            'relPhoto'=> array(self::BELONGS_TO, 'Photo', 'photoid'),
            'relFst'=> array(self::BELONGS_TO, 'Fst', 'FstID'),
            'relCommand'=> array(self::BELONGS_TO, 'Command', 'CommandID'),
            'relCategory'=> array(self::BELONGS_TO, 'Sportcategory', 'CategoryID'),
            'relAttestlevel'=> array(self::BELONGS_TO, 'Attestlevel', 'AttestLevelID'),
            'relAgecategory'=> array(self::BELONGS_TO, 'Agecategory', 'AgeID'),
            'relWeightcategory'=> array(self::BELONGS_TO, 'Weightcategory', 'WeigthID'),
            'relCoach'=> array(self::BELONGS_TO, 'Coach', 'Coach1ID'),
            'relCoachFirst'=> array(self::BELONGS_TO, 'Coach', 'Coach2ID'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'SpID' => Yii::t('fullnames', 'ID'),
			'LastName' => Yii::t('fullnames', 'LastName'),
			'FirstName' => Yii::t('fullnames', 'FirstName'),
			'MiddleName' => Yii::t('fullnames', 'MiddleName'),
            'BirthDate' => Yii::t('fullnames', 'BirthDate'),
			'fullyears' => Yii::t('fullnames', 'Full years'),
			'Gender' => Yii::t('fullnames', 'Gender'),
			'CommandID' => Yii::t('fullnames', 'CommandName'),
			'FstID' => Yii::t('fullnames', 'FstName'),
			'CategoryID' => Yii::t('fullnames', 'CategoryName'),
			'AttestLevelID' => Yii::t('fullnames', 'AttestLevelName'),
			'WeigthID' => Yii::t('fullnames', 'WeightName'),
			'Coach1ID' => Yii::t('fullnames', 'Coach'),
			'Coach2ID' => Yii::t('fullnames', 'CoachFirst'),
			'MedicSolve' => 'Мед.',
            
            'FullName' => Yii::t('fullnames', 'FullName'),
            'FstName' => Yii::t('fullnames', 'FstName'),
            'CategoryName' => Yii::t('fullnames', 'CategoryName'),
            'AttestLevelName' => Yii::t('fullnames', 'AttestLevelName'),
            'AgeID' => Yii::t('fullnames', 'AgeName'),
            'AgeName' => Yii::t('fullnames', 'AgeName'),
            'WeightName' => Yii::t('fullnames', 'WeightName'),
            'WeightNameFull' => Yii::t('fullnames', 'WeightNameFull'),
            'BirthYear' => Yii::t('fullnames', 'BirthYear'),
            'IdentCode' => Yii::t('fullnames', 'IdentCode'),
            
            'persontul' => 'Личный туль',
		);
	}

    /**
    * поведегния для модели
    * 
    */
    public function behaviors()
    {
        return array(
            // поведение для логирования
            'ActiveRecordLogableBehavior'=>'application.behaviors.ActiveRecordLogableBehavior',
        );
    }    
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('SpID',$this->SpID);
		$criteria->compare('LastName',$this->LastName,true);
		$criteria->compare('FirstName',$this->FirstName,true);
		$criteria->compare('MiddleName',$this->MiddleName,true);
		$criteria->compare('BirthDate',$this->BirthDate,true);
		$criteria->compare('Gender',$this->Gender,true);
		$criteria->compare('CommandID',$this->CommandID);
		$criteria->compare('FstID',$this->FstID);
		$criteria->compare('CategoryID',$this->CategoryID);
		$criteria->compare('AttestLevelID',$this->AttestLevelID);
		$criteria->compare('WeigthID',$this->WeigthID);
		$criteria->compare('Coach1ID',$this->Coach1ID);
		$criteria->compare('Coach2ID',$this->Coach2ID);
		$criteria->compare('MedicSolve',$this->MedicSolve);
        
        $criteria->compare('AgeID',$this->AgeID);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    //Jurets: получить год рождения из даты
    public function BirthYear() {
        //$d = $this->BirthDate;
        //return date('Y', $d);
        return substr($this->BirthDate, 0, 4); ///тоже коряво
    }
    
    //Jurets: составить полное ФИО спортсмена
    public function FullName() {
        return $this->LastName.' '.$this->FirstName;
    }
    
    //Jurets: получить название команды (по ID)
    public function CommandName() {
        $record = Command::model()->findByPk($this->CommandID);
        if (isset($record) && !is_null($record))
            return $record->getAttribute('CommandName');
    }

    //Jurets: получить название ФСТ (по ID)
    public function FstName() {
        $record = Fst::model()->findByPk($this->FstID);
        if (isset($record) && !is_null($record))
            return $record->getAttribute('FstName');
    }

    //Jurets: получить 1-го тренера (по ID)
    public function Coach1Name() {
        //$record = Coach::model()->findByPk($this->Coach1ID);
        $data = self::getCoachList($this->CommandID);
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->CoachID == $this->Coach1ID) {
                $record = $item;
                break;
            }
        if (isset($record) && !is_null($record))
            return $record->getAttribute('CoachName');
    }

    //Jurets: получить тренера (по ID)
    public function Coach2Name() {
        //$record = Coach::model()->findByPk($this->Coach2ID);
        $data = Sportsmen::getCoachList($this->CommandID);
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->CoachID == $this->Coach2ID) {
                $record = $item;
                break;
            }
        if (isset($record) && !is_null($record))
            return $record->getAttribute('CoachName');
    }

    //Jurets: получить название спортивного разряда (по ID)
    public function CategoryName() {
        $data = Sportcategory::getList();
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->CategoryID == $this->CategoryID) {
                $record = $item;
                break;
            }
        if (isset($record) && !is_null($record))
            return $record->getAttribute('CategoryName');
    }
    
    //Jurets: получить название спортивного разряда (по ID)
    public function AttestLevelName() {
        $data = Attestlevel::getList();
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->AttestLevelID == $this->AttestLevelID) {
                $record = $item;
                break;
            }    
        if (isset($record) && !is_null($record))
            return $record->getAttribute('AttestLevel');
    }
    
    //Jurets: получить название возрастной категории (по ID)
    public function AgeName() {
        $data = Agecategory::getAges();
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->AgeID == $this->AgeID) {
                $record = $item;
                break;
            }
        if (isset($record) && !is_null($record)) {
            $aname = $record->getAttribute('AgeName');
            $ymin = $record->getAttribute('YearMin');
            $ymax = $record->getAttribute('YearMax');
            if (!is_null($ymin) || !is_null($ymax)) {
                $aname .= ' (';
                if (!is_null($ymin))
                    $aname .= $ymin;
                if (!is_null($ymax))
                    $aname .= ' - '.$ymax;
                $aname .= ' р.н.)';
            }
            return $aname;
        }
    }
    
    //Jurets: получить название весовой категории (по ID)
    public function WeightName() {
        $data = Sportsmen::getWeigthsList($this->AgeID);
      //пройтись по массиву и выбрать только нужный итем
        foreach($data as $index => $item) 
            if ($item->WeightID == $this->WeigthID) {
                $record = $item;
                break;
            }
        if (is_null($record))
            return;
        $wvalue = $record->getAttribute('WeightTo');
        if (is_null($wvalue)) {
            $wvalue = $record->getAttribute('WeightFrom');
            $str = 'свыше';
        } else {
            $str = 'до';
        }
        return $str.' '.$wvalue.' кг';
    }
    
    //Jurets: получить название весовой категории (по ID)
    public function WeightNameFull() {
        $wn = $this->WeightName();
        if (isset($wn) && !is_null($wn))
            return mb_strtoupper($this->Gender, 'UTF-8').' '.$wn;
    }
    
    //СТАТ: узнать количество спортсменов
    /*static function getSportsmenCount() {
        $count = Yii::app()->db->createCommand('SELECT COUNT(*) FROM sportsmen')->queryScalar();
        return $count;
    }*/
    
    //СТАТ: узнать количество спортсменов вес.категории
    static function getSportsmenCount($weigthid = null) {
        /*$sqlCommand = Yii::app()->db->createCommand()
            ->select(array('COUNT(*)'))
            ->from('sportsmen')
            ->where('status = 1');
        if (!empty($weigthid))
            $sqlCommand->where()[] = 'weigthid = '.$weigthid*/

        //$sql = 'SELECT COUNT(*) FROM sportsmen'.(isset($weigthid) ? ' where weigthid = '.$weigthid : '');

        $sql = 'SELECT COUNT(*) FROM sportsmen S LEFT JOIN command D ON D.commandid = S.commandid '.
            'WHERE D.competitionid = ' . Yii::app()->competitionId .
            (isset($weigthid) ? ' AND weigthid = '.$weigthid : '');
        
        $dependency = new CDbCacheDependency($sql);
        $count = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryScalar();
        return $count;
    }

    //СТАТ: узнать ограничение по кол-ву спортсменов
    /*static function getSpMaxLimitCount() {
        $count = Yii::app()->db->createCommand('SELECT maxparticipants FROM competition LIMIT 1')->queryScalar();
        return $count;
    }*/
    
    

    //ЗАПРОС: список спортсменов команды
    static public function sqlSportsmenList($CommandID = null, $filter = array()) {
        $arrFields = array('S.SpID',
                           'CONCAT(S.lastname, " ", S.firstname) AS FullName');
        if (!isset($CommandID) || empty($CommandID))
            $arrFields[] = 'D.Commandname';    
        $arrFields = array_merge($arrFields, array(
                           'F.FstName',
                           'C.CategoryName',
                           'L.Attestlevel AS AttestLevelName',
                           'YEAR(S.birthdate) AS BirthYear',
                           'A.AgeName',
                           'IF(W.WeightTo IS NULL, CONCAT(W.WeightFrom, "+"), CONCAT("-", W.WeightTo)) AS WeightNameFull',
                           'C1.Coachname',
                           'C2.Coachname as Coachname1'
                           )
                     );
        $sqlCommand = Yii::app()->db->createCommand()
            ->select($arrFields)
            ->from('sportsmen S')
            ->leftJoin('fst F', 'S.fstid = F.fstid')
            ->leftJoin('sportcategory C', 'S.categoryid = C.categoryid')
            ->leftJoin('attestlevel L', 'S.attestlevelid = L.attestlevelid')
            ->leftJoin('weightcategory W', 'W.weightID = S.weigthid')
            ->leftJoin('agecategory A', 'A.Ageid = W.Ageid')
            ->leftJoin('coach C2', 'C2.coachid = S.coach2id')
            ->leftJoin('coach C1', 'C1.coachid = S.coach1id');
        if (isset($CommandID) && !empty($CommandID)) {
            $sqlCommand->where('S.status = 1 AND S.commandid = '.$CommandID);
        } else {
            $sqlCommand
            ->leftJoin('command D', 'D.commandid = S.commandid')
            //->where('S.status = 1');
            ->where('S.status = 1 AND D.competitionid = '.Yii::app()->competitionId);
        }
        if (!empty($filter)) {
            $fields = array();
            $values = array();
            foreach($filter as $key=>$value) {
                if (strtolower($key) == 'fullname') {
                    $condition = 'CONCAT(S.lastname, " ", S.firstname) LIKE :'.$key;
                } else if (strtolower($key) == 'fstname') {
                    $condition = 'FstID = :'.$key;
                }
                $field = $key;
                $fields[] = $field.' like :'.$key;
                $values[':'.$key] = '%'.$value.'%';
            }
            $fields = implode(' AND ', $fields);
            $sqlCommand->andWhere($fields, $values);
            $sqlCommand->params = $values;
        }
        //$sqlCommand->order('FullName');
        return $sqlCommand;
    }

    //ЗАПРОС: список ВСЕХ спортсменов всех категорий, отсортированы
    //  используется для показа жеребьёвки:
    //  - проход в цикле по возрастным и весовым категориям 
    //  - затем фильтруется по весовой категории
    static public function sqlTosserList() {
        $sqlCommand = Yii::app()->db->createCommand()
            ->select(array('S.SpID',
                   'CONCAT(S.lastname, " ", S.firstname) AS FullName',
                   'D.Commandname',
                   'F.FstName',
                   'C.CategoryName',
                   'YEAR(S.birthdate) AS BirthYear',
                   'C1.Coachname',
                   'C2.Coachname as Coachname1',
                   'S.AgeID',
                   'S.WeigthID',
                   'S.TossNum',
                   'A.AttestLevel',
                   'S.persontul',
                   'S.fullyears'))
            ->from('sportsmen S')
            ->leftJoin('command D', 'D.commandid = S.commandid')
            ->leftJoin('fst F', 'S.fstid = F.fstid')
            ->leftJoin('sportcategory C', 'S.categoryid = C.categoryid')
            ->leftJoin('coach C2', 'C2.coachid = S.coach2id')
            ->leftJoin('coach C1', 'C1.coachid = S.coach1id')
            ->leftJoin('attestlevel A', 'A.AttestLevelID = S.AttestLevelID')
            ->where('S.status = 1 AND D.competitionid = '. Yii::app()->competitionId)
            ->order('S.AgeID, S.WeigthID, FullName');
        return $sqlCommand;
    }
    
    //ЗАПРОС: список спортсменов весовой категории
    static public function sqlWeightmenList($weigthid = null) {
        $sqlCommand = Yii::app()->db->createCommand()
            ->select(array('S.SpID',
                   'CONCAT(S.lastname, " ", S.firstname) AS FullName',
                   'D.Commandname',
                   'F.FstName',
                   'C.CategoryName',
                   'YEAR(S.birthdate) AS BirthYear',
                   'C1.Coachname',
                   'C2.Coachname as Coachname1'))
            ->from('sportsmen S')
            ->leftJoin('command D', 'D.commandid = S.commandid')
            ->leftJoin('fst F', 'S.fstid = F.fstid')
            ->leftJoin('sportcategory C', 'S.categoryid = C.categoryid')
            ->leftJoin('coach C2', 'C2.coachid = S.coach2id')
            ->leftJoin('coach C1', 'C1.coachid = S.coach1id')
            ->where('S.status = 1 AND weigthid = '.$weigthid)
            ->order('FullName');
        return $sqlCommand;
    }

  //получить список возрастных категорий по полу (КЭШИРУЕТСЯ!)
    public static function getAgesList($gender = null) {
        $out = array();
        $_cacheID = 'cacheAgeListFull';
        $data = Yii::app()->cache->get($_cacheID);   //проверить кэш
        if ($data === false) {
            // устанавливаем значение $value заново, т.к. оно не найдено в кэше,
            $data = Agecategory::model()->findAll('AgeID not in (9,10)');
            // и сохраняем его в кэше для дальнейшего использования:
            Yii::app()->cache->set($_cacheID, $data, 28800);  //8 часов
        }
      //пройтись по массиву и выбрать только нужный пол  
        foreach($data as $index => $item) {
            if (empty($gender) || $item->Gender == $gender)
                $out[] = $item;
        }
        return $out;
    }
    
  //получить список весовых категорий по возрасту (КЭШИРУЕТСЯ!)
    public static function getWeigthsList($ageid) {//DebugBreak();
        $out = array();
        $_cacheID = 'cacheWeigthListFull';
        //$data = Yii::app()->cache->get($_cacheID);   //проверить кэш
        //if ($data === false) 
        {
            // устанавливаем значение $value заново, т.к. оно не найдено в кэше,
            $data = Weightcategory::model()->findAll();
            // и сохраняем его в кэше для дальнейшего использования:
            //Yii::app()->cache->set($_cacheID, $data, 28800);  //8 часов
        }
      //пройтись по массиву и выбрать только нужный пол  
        foreach($data as $index => $item) {
            if ($item->AgeID == $ageid)
                $out[] = $item;
        }
        return $out;
    }

  //получить список тренеров по команде (КЭШИРУЕТСЯ!)
    public static function getCoachList($commandid) {
        $out = array();
        $_cacheID = 'cacheCoachListFull' . Yii::app()->competitionId;
        $data = Yii::app()->cache->get($_cacheID);   //проверить кэш
        if ($data === false) {
            // устанавливаем значение $value заново, т.к. оно не найдено в кэше,
            $data = Coach::model()->findAll();
            // и сохраняем его в кэше для дальнейшего использования:
            Yii::app()->cache->set($_cacheID, $data, 60);  //1 минут
        }
      //пройтись по массиву и выбрать только нужную команду
        foreach($data as $index => $item) {
            if ($item->CommandID == $commandid)
                $out[] = $item;
        }
        return $out;
    }

    public function getAgeName() {
        return isset($this->relAgecategory) ? $this->relAgecategory->AgeName : null;
    }
    public function getFstName() {
        return isset($this->relFst) ? $this->relFst->FstName : null;
    }
    public function getAttestLevelName() {
        return isset($this->relAttestlevel) ? $this->relAttestlevel->AttestLevel : null;
    }
    public function getCategoryName() {
        return isset($this->relCategory) ? $this->relCategory->CategoryName : null;
    }
    public function getCoachName() {
        return isset($this->relCoach) ? $this->relCoach->CoachName : null;
    }
    public function getCoachFirstName() {
        return isset($this->relCoachFirst) ? $this->relCoachFirst->CoachName : null;
    }
    
    //Jurets: получить название весовой категории (по ID)
    public function getWeightNameFull() {
        return isset($this->relWeightcategory) ? $this->relWeightcategory->WeightNameFull : null;
    }
    //Jurets: получить название весовой категории (по ID)
    public function getWeightNameShort() {
        return isset($this->relWeightcategory) ? $this->relWeightcategory->WeightNameShort : null;
    }
    
    /**
    * выдать HTML-код показа весовой категории с дропдаун-меню для её смены 
    * 
    */
    public function getWeightSelectWidget() {
        /*$widget = Yii::app()->controller->widget('bootstrap.widgets.TbButton', array(
                    'id'=>'sp_'.$this->SpID,
                    'label'=>'EXAMPLE',
                    'items'=>array(
                        array('label'=>'Home', 'url'=>array('site/index')),
                        array('label'=>'Products', 'url'=>array('product/index')), 
                        array('label'=>'Login', 'url'=>array('site/login'), 'visible'=>Yii::app()->user->isGuest),
                    ),
                ), true);
        $widget = CHtml::openTag('div', array('style'=>"position: relative;")) .
                    $widget .
                  CHtml::closeTag('div');*/
        $widget = 
'<div class="dropdown">
  <a class="btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
    Dropdown
    <span class="caret"></span>
  </a>
  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Another action</a></li>
    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
    <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
  </ul>
</div>';
   //     DebugBreak();
        if (!isset($this->_weightlist[$this->AgeID]))
        $this->_weightlist[$this->AgeID] = self::getWeigthsList($this->AgeID);
        $list = '';
        foreach ($this->_weightlist[$this->AgeID] as $item) {
            $list .= $item;
        }
        $widget = 
            '<div class="dropdown">
              <a class="btn-default dropdown-toggle" type="button" id="weightslist' . $this->SpID . '" data-toggle="dropdown" aria-expanded="true">
                ' . $this->getWeightNameShort() . '
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                   ' . $list . '
              </ul>
            </div>';
        return $widget;
    }
    
    /**
    * перед валидацией - установить пол (если не был установлен)
    * 
    */
    public function beforeValidate() {//DebugBreak();
        //if (!isset($this->Gender) || empty($this->Gender)) 
        {
            if (isset($this->AgeID)) {
                //$age = Agecategory::model()->findByPk($this->AgeID);
                $ages = Agecategory::getAges();
                foreach($ages as $age) {
                    if ($age->AgeID == $this->AgeID && isset($age->Gender)) {
                        $this->Gender = $age->Gender;
                        break;
                    }
                }
            }
        }
        return true;
    }
    
    //список заявок на соревнования
    public function getChangeLog() {
        $sqlCommand = Yii::app()->db->createCommand()
            ->select(array('A.id', 'A.userid', 'A.action', 'A.field', 'A.creationdate', 'A.description', 'concat(U.lastname, " ", U.firstname) as username'))
            ->from('activerecordlog A')
            ->leftJoin('user U', 'U.UserName = A.userid')
            ->where(array('AND', 'idmodel = :idmodel', 'model = :model'))
            ->order('creationdate DESC');
        $dataProvider = new CSqlDataProvider($sqlCommand->text, array('params'=>array(':idmodel'=>$this->SpID, ':model'=>"Sportsmen")));
        return $dataProvider;
    }    
    
    //выборка алиаса атрибута
    public function getAttributeLabel($name) {
        $array = $this->attributeLabels();
        return array_key_exists($name, $array) ? $array[$name] : $name;
    }
}
