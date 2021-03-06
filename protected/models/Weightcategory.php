<?php

/**
 * This is the model class for table "weightcategory".
 *
 * The followings are the available columns in table 'weightcategory':
 * @property integer $WeightID
 * @property integer $AgeID
 * @property string $WeightName
 * @property integer $WeightFrom
 * @property integer $WeightTo
 */
class Weightcategory extends CActiveRecord
{
    // статусы страницы жеребьёвки
    const TOSSER_NEW = 0;
    const TOSSER_ACTIVE = 1;
    const TOSSER_WAIT = 2;
    
    public $tosserGrid = array(
        'grids'=>array(
            '16'=>array(
                'levelcount'=>5,
                'levels'=>array(
                    5=>array(
                        1=>array(1,32),
                        2=>array(16,17),
                        3=>array(9,24),
                        4=>array(8,25),
                        5=>array(5,28),
                        6=>array(12,21),
                        7=>array(13,20),
                        8=>array(4,29),
                        9=>array(3,30),
                       10=>array(14,19),
                       11=>array(11,22),
                       12=>array(6,27),
                       13=>array(7,26),
                       14=>array(10,23),
                       15=>array(15,18),
                       16=>array(2,31),
                    ),
                    4=>array(
                        1=>array(1,16),
                        2=>array(9,8),
                        3=>array(5,12),
                        4=>array(13,4),
                        5=>array(3,14),
                        6=>array(11,6),
                        7=>array(7,10),
                        8=>array(15,2),
                    ),
                    3=>array(
                        1=>array(1,8),
                        2=>array(5,4),
                        3=>array(3,6),
                        4=>array(7,2),
                    ),
                    2=>array(
                        1=>array(1,4),
                        2=>array(3,2),
                    ),
                    1=>array(
                        1=>array(1,2),
                    ),
                )
            )
        )
    );

    private $_tosserManager = array(
        'levels'=>array(
            5=>array(
                1=>array(1,32),
                2=>array(16,17),
                3=>array(9,24),
                4=>array(8,25),
                5=>array(5,28),
                6=>array(12,21),
                7=>array(13,20),
                8=>array(4,29),
                9=>array(3,30),
               10=>array(14,19),
               11=>array(11,22),
               12=>array(6,27),
               13=>array(7,26),
               14=>array(10,23),
               15=>array(15,18),
               16=>array(2,31),
            ),
            4=>array(
                1=>array(1,16),
                2=>array(9,8),
                3=>array(5,12),
                4=>array(13,4),
                5=>array(3,14),
                6=>array(11,6),
                7=>array(7,10),
                8=>array(15,2),
            ),
            3=>array(
                1=>array(1,8),
                2=>array(5,4),
                3=>array(3,6),
                4=>array(7,2),
            ),
            2=>array(
                1=>array(1,4),
                2=>array(3,2),
            ),
            1=>array(
                1=>array(1,2),
            ),
        ),
        'grids'=>array(
            '4'=>array(
                'levelcount'=>2,
            ),
            '8'=>array(
                'levelcount'=>3,
            ),
            '16'=>array(
                'levelcount'=>4,
            ),
            '32'=>array(
                'levelcount'=>5,
            ),
            '64'=>array(
                'levelcount'=>6,
            ),
        )
    );
    
    function getTosserManager() {
        return $this->_tosserManager;
    }
    
	// Returns the static model of the specified AR class.
	// * @param string $className active record class name. * @return Weightcategory the static model class
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	// @return string the associated database table name
	public function tableName()
	{
		return 'weightcategory';
	}

	// @return array validation rules for model attributes.
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            //array('AgeID, WeightName', 'required'),
			array('AgeID', 'required'),
			array('AgeID, WeightFrom, WeightTo', 'numerical', 'integerOnly'=>true),
			array('WeightName', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('WeightID, AgeID, WeightName, WeightFrom, WeightTo', 'safe', 'on'=>'search'),
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
            'relAgecategory'=> array(self::BELONGS_TO, 'Agecategory', 'AgeID'),
            'countSportsmen'=> array(self::STAT, 'Sportsmen', 'WeigthID'),
		);
	}

    /**
    * put your comment there...
    * 
    */
    public function defaultScope() {
        return array(
            //'order'=>$this->getTableAlias().'ordernum, '.$this->getTableAlias().'AgeID ASC'
            'alias'=>'weigth',
            'order'=>'weigth.ordernum, weigth.AgeID ASC'
        );
    }
        
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'WeightID' => 'Weight',
			'AgeID' => 'Age',
			'WeightName' => 'Weight Name',
			'WeightFrom' => 'Weight From',
			'WeightTo' => 'Weight To',
		);
	}

    /**
    * после сохранения объекта
    */
    public function afterSave() {
        $_cacheID = 'cacheAgeList' . Yii::app()->competitionId;
        Yii::app()->cache->delete($_cacheID);  //очистить кэш
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

		$criteria->compare('WeightID',$this->WeightID);
		$criteria->compare('AgeID',$this->AgeID);
		$criteria->compare('WeightName',$this->WeightName,true);
		$criteria->compare('WeightFrom',$this->WeightFrom);
		$criteria->compare('WeightTo',$this->WeightTo);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
    
    //Jurets: получить название весовой категории (по ID)
    public function getWeightNameFull() {
        if ($this->WeightID == -1)
            return null;
        if (is_null($this->WeightTo)) 
            return 'свыше '.$this->WeightFrom.' кг';
        else 
            return 'до '.$this->WeightTo.' кг';
    }
    
    //Jurets: получить название весовой категории (по ID)
    public function getWeightNameShort() {
        if ($this->WeightID == -1)
            return null;
        if (is_null($this->WeightTo)) 
            return '+'.$this->WeightFrom.'кг';
        else 
            return '-'.$this->WeightTo.'кг';
    }
    
    //Функция: вычисление числа уровней по кол-ву боёв
    public static function getFigthCount($sportsmencount) {
        return $sportsmencount - 1;
    }

    //Функция: вычисление числа уровней сетки по кол-ву боёв
    public static function getLevelCount($figthcount) {
        if ($figthcount <= 0)
            return 0;
        $levelCount = 1;
        while ($figthcount > 0) {
            $figthcount = (int)($figthcount / 2);
            if ($figthcount)
                $levelCount++;
        }
        return $levelCount;
    }
    
  //получить список весовых категорий по возрасту
    public static function getWeigthsArray($ageid = null) {//DebugBreak();
        $sqlCommand = Yii::app()->db->createCommand()
            ->select(array('WeightID', 'S.TossNum'))
            ->from('weightcategory')
            //->where('status = 1 AND D.competitionid = '. Yii::app()->competitionId)
            ->order('AgeID, WeigthID');
        return $sqlCommand;
    }
    
  //получить список весовых категорий по возрасту (КЭШИРУЕТСЯ!)
    public static function getWeigthsList($ageid = null) {//DebugBreak();
        if (isset($ageid)) {
            $data = Weightcategory::model()->findAllByAttributes(array('AgeID'=>$ageid));
        } else {
            $data = Weightcategory::model()->findAll();
        }
        return $data;
    }
    
    /**
    * получить максимальный номер по порядку в пределах соревнования
    * 
    */
    public function getMaxOrdernum($id) {
        $compid = Yii::app()->CompetitionID;
        return Yii::app()->db->createCommand()
            ->select('COALESCE(MAX(ordernum), 0)')
            ->from($this->tableName())
            ->where('AgeID = :AgeID', array(':AgeID'=>$id))
            ->queryScalar();
    }    
}