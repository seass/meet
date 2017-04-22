<?php
namespace Util\Qrcode;
use Util\Qrcode\QRcode;
/**
 * 二维码生成
 *
 * @author sea
 */
// 二维码生成
class QR
{
    // 二维码内容
    private $value;
    // 容错级别分别是 L（QR_ECLEVEL_L，7%），M（QR_ECLEVEL_M，15%），Q（QR_ECLEVEL_Q，25%），H（QR_ECLEVEL_H，30%）；
    private $errorCorrectionLevel;
    // 生成图片大小,默认为6，
    private $matrixPointSize;
    // 原生图片
    public  $QR;
    // 图片名称
    private $QrName;
    // 最终图片保存路径
    private $QrYPath;
    // 最终图片保存路径
    private $QrLPath;

    public function __construct($value, $qrarray = array())
    {
        
        $this->QrYPath='./Data/'."QrImage/";
        $this->QrLPath="./Data/Back/";
        
        $this->value = isset($value) ? $value : '';
        $this->errorCorrectionLevel = isset($qrarray['errorCorrectionLevel']) ? $qrarray['errorCorrectionLevel'] : 'H';
        $this->matrixPointSize = isset($qrarray['matrixPointSize']) ? $qrarray['matrixPointSize'] : 6;
        //\Think\Log::record("SEAHH:".$this->QrYPath);
        //echo $this->QrYPath;
        if (! is_dir($this->QrYPath)) {
            mkdir($this->QrYPath, 0777, true);
        }
        $this->QrName = isset($qrarray['QrName']) ? $qrarray['QrName'] : 'qr.png';
        $this->QR = $this->QrYPath . $this->QrName;
        QRcode::png($this->value, $this->QR, $this->errorCorrectionLevel, $this->matrixPointSize, 2);
    }

}