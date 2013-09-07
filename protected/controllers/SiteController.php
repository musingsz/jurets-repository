<?php

class SiteController extends Controller
{

    //public $layout='//layouts/column0';
    
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$competition = Competition::loadModel();
        $this->render('index', array('competition'=>$competition));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			//проверка входящего юзера и редирект:
			if($model->validate() && $model->login()) {
                //если текущий урл = главному, то определить по команде
                $currenturl = Yii::app()->user->returnUrl;
                $homeurl = Yii::app()->homeUrl;
                if ($currenturl == $homeurl || $currenturl == $homeurl.'index.php') {
                    if (Yii::app()->user->isExtendRole())
                        $url = $this->createUrl('/users/index');
                    else {
                        $commandid = Yii::app()->user->getCommandID(); //определить команду вошедшего юзера
                        if (Yii::app()->isUserActive && isset($commandid) && !empty($commandid))
                            $url = $this->createUrl('/command/view',array('id'=>$commandid));
                        else
                            $url = $this->createUrl('/users/mycabinet');
                    }
                    //если Урл пустой - задать ретурнУрл
                    if (!isset($url) || empty($url))
                        $url = $this->createUrl('/command/index');//Yii::app()->user->returnUrl;
                }
                else
                    $url = $currenturl;
                $this->redirect($url);
            }
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

    //
    public function actionManager()
    {
        $this->render('manager',array(
            'dataProposalList'=>new CActiveDataProvider('Proposal', array(
                'pagination'=>array(
                    'pageSize'=>20,
                    ),
                )))
        );
    }
    
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{//DebugBreak();
        $urlReferrer = Yii::app()->request->urlReferrer;
        $urlReferrer = strtolower($urlReferrer);
        if (strpos($urlReferrer, 'proposal') || strpos($urlReferrer, 'users'))
            $urlRedirect = Yii::app()->homeUrl;
        else
            $urlRedirect = !empty($urlReferrer) ? $urlReferrer : Yii::app()->homeUrl;
		Yii::app()->user->logout();
        $this->redirect($urlRedirect);
	}
    
}