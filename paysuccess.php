<h1>Платёж успешно завершён!</h1>
<a href="http://tkd-card.com.ua/payment.php">вернуться на страницу оплаты</a>
<?php
    //DebugBreak();
    $filename = 'saveddata.json';
    if (is_file($filename)) {
        $data = file_get_contents($filename);
        $data = json_decode($data, true);
        $ik_lastpay_num = $data['ik_lastpay_num'];
    } else {
        $ik_lastpay_num = 0;
        
    }
    $ik_lastpay_num = $ik_lastpay_num + 1;
    $data = array('ik_lastpay_num'=>$ik_lastpay_num);
    $data = json_encode($data);
    file_put_contents($filename, $data);

    if (!empty($_POST)) {
        //чтото делаем
        //print_r($_POST); ?>
        <table>
            <tbody>
                <tr>
                    <td>Дата платежа</td>
                    <td><?=$_POST['ik_inv_prc']?></td>
                </tr>
                <tr>
                    <td>Сумма</td>
                    <td><?=$_POST['ik_am']?></td>
                </tr> 
                <tr>
                    <td>Назначение</td>
                    <td><?=$_POST['ik_desc']?></td>
                </tr>
            </tbody>
        </table>
        
    <?php }

?>