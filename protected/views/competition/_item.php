<?php
     $isVisible = !Yii::app()->user->isGuest && !Yii::app()->user->isExtendRole() && $data->isfiling;
?>
<li class="media" style="border-bottom: 1px gray;">
    <!--<a class="pull-left" href="<?=Yii::app()->createAbsoluteUrl('competition/invite', array('id'=>$data->id))?>">-->
    <a class="pull-left" href="<?=Yii::app()->createAbsoluteUrl($data->path)?>">
        <img class="media-object" src="<?=Yii::app()->baseUrl?>/images/tkd_57x60.png" alt="competition">
    </a>
    <div class="media-body">
        <h4 class="media-heading"><?=$data->title?></h4>
        <p><?=DateHelper::dateToRus($data->begindate)?> - <?=DateHelper::dateToRus($data->enddate)?><p>
        <p><?=$data->place?></p>
        <!--<a class="button btn" href="<?=Yii::app()->createAbsoluteUrl($data->path . '/proposal/create', array())?>">Подать заявку</a>-->
        <?php 
        $this->widget('bootstrap.widgets.TbLabel', array(
            'type'=>($data->isfiling ? 'success' : 'important'), // 'success', 'warning', 'important', 'info' or 'inverse'
            'label'=>($data->isfiling ? 'заявки принимаются' : 'приём заявок окончен'),
        )); 
        
        if ($isVisible) { 
            echo CHtml::tag('a', array('class'=>"button btn", 'href'=>Yii::app()->createAbsoluteUrl($data->path . '/proposal/create')), 'Подать заявку');
        } ?>
    </div>
</li>