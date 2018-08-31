<?php
/**
 * Created by PhpStorm.
 * User: lovelessjack
 * Date: 2018/8/29
 * Time: 22:24
 */

namespace app\api\controller;

use app\api\library\ApiSpeech\AipSpeech;
use think\Controller;
use think\Exception;

/** 小程序 语音 上传服务器 && 接入 科大讯飞语音识别 （项目通用Interface）
 * // TODO 记得每周凌晨4点清理一下 public/uploads/Aduio文件夹里的 mp3 pcm wav 格式文件
 * Class SpeechVoice
 * @package app\api\controller
 */
class Speechvoice extends Controller
{

    /**
     * 自行申请创建百度语音应用 APP_ID API_KEY SECRET_KEY
     */
    const APP_ID = '********';
    const API_KEY = 'ym8***mN3***AlK***KMD***';
    const SECRET_KEY = 'exA***frw***tCe***bpv***Mk3***Y6';

    /** 微信语音上传音频 （请记得微信小程序后台开发配置上 requests域名、uploadFile域名，要不然上传不了语音，报错500）
     * @return \think\response\Json
     */
    public function wxupload()
    {
        $upload_res = $_FILES['voice'];
        $tempfile = file_get_contents($upload_res['tmp_name']);
        $pcmname = substr($upload_res['name'], 0, strripos($upload_res['name'], ".")) . ".wav";

        $arr = explode(",", $tempfile);
        $path = ROOT_PATH . 'public/uploads/Aduio/' . $upload_res['name'];

        if ($arr && !empty(strstr($tempfile, 'base64'))) {

            //微信模拟器录制的音频文件可以直接存储返回
            file_put_contents($path, base64_decode($arr[1]));
            $data['path'] = $path;
            return $this->apiResponse("success", "转码成功！", $data);

        } else {

            //手机录音文件
            $path = ROOT_PATH . 'public/uploads/Aduio/' . $upload_res['name'];
            $newpath = ROOT_PATH . 'public/uploads/Aduio/' . $pcmname;

            try {

//                file_put_contents($path, $tempfile);

//                example： ffmpeg -i test.mp3 -ar 16000 -ac 1 -acodec pcm_s16le wav16k.wav
                /** 转码科普
                 * -ar 采样率 16000 最好与微信小程序语音的发送 采样率保持一致。 还有调取百度语音接口 采样率也要保持一致，否则会识别语音乱七八糟。
                 * -ac 1 单声道 2 双声道
                 * pcm_s16le {
                 * s8:     signed   8 bits
                 * s16:     signed   16 bits
                 * s24:     signed   24 bits
                 * s32:     signed   32 bits
                 * s是有符号 pcm_s16be pcm_s16le
                 * u是无符号 pcm_u16be pcm_u16le
                 * be 是大端（低地址存高位）pcm_s16be [o=>11,1=>22,2=>33,3=>44] 低地址 -----> 高地址 尾端44
                 * le 是小端（低地址存低位）pcm_s16le [0=>44,1=>33,2=>22,3=>11] 高地址 -----> 低地址 尾端11
                 * }
                 */
                /** 科普 为什么不直接用ffmpeg command 执行转码命令 而使用 绝对路径
                 * No.1 ffmpeg 直接执行命令 exec command 第三个$status参数会返回127错误，
                 * 若返回1 错误 代表类似"除零"的杂乱错误 （语音是放不出来的裸数据，不代表没数据，需要用Autacity 音频软件导入this.裸数据进去播放才能听出来。）
                 * 返回0 代表成功转码，下载下来可以直接电脑放出自己发出的语音。
                 * /usr/local/ffmpeg/bin/ffmpeg 返回$status 0
                 */
                $exec1 = "/usr/local/ffmpeg/bin/ffmpeg -i $path -ar 16000 -ac 1 -acodec pcm_s16le $newpath";
//                file_put_contents(ROOT_PATH . 'public/uploads/Aduio/exec.log', $exec1);
                exec($exec1, $info, $status);

            } catch (Exception $e) {
                echo $e->getMessage();
            }

            if (!empty($tempfile) && $status == 0) {

                return $this->getVoice($newpath);

            }

        }
        return $this->apiResponse("error", "发生未知错误！");
    }


    /** 请求百度语音识别接口数据处理
     * @param $path string 语音文件路径
     * @return \think\response\Json
     */
    public function getVoice($path)
    {
        /** @var array $result */
        $client = new AipSpeech(self::APP_ID, self::API_KEY, self::SECRET_KEY);

        /**
         * 注意格式要跟转码时候设置的一样。 ROOT_PATH*********.wav wav
         * 16000 采样率
         * dev_pid 普通话
         */
        $result = $client->asr(file_get_contents($path), 'wav', 16000, array('dev_pid' => 1536));

        if (!is_array($result)) {
            $array = '没听清楚你说的话呀！';
            return json($array, 200);
        }

        try {
            $results = json_decode(json_encode($result), true);
        } catch (Exception $e) {
            $array = '识别有误呀，没听清！';
            return json($array, 200);
        }

        /**
         * 注意百度语音识别的err_msg的返回值
         * 是 success.
         * 是 success.
         * 是 success.
         * 重要的事情说三遍，我就是因为少了一个点，耽误了20分钟😡......
         * 一定要仔细看文档，它是success. 多了个英文...... 点
         */
        if ($results['err_msg'] === 'success.' && $results['err_no'] === 0) {
            $array['query_text'] = $results['result'][0];
            return json($array, 200);
        } else {
            $array['query_text'] = 'NULL';
            return json($array);
        }
    }

    /**
     * @param $path object 语音文件
     * @return \think\response\Json
     */
    public function getXfyun($path)
    {
        $d = base64_encode($path);
        $url = "http://api.xfyun.cn/v1/service/v1/iat";
        $xparam = base64_encode(json_encode(array('engine_type' => 'sms16k', 'aue' => 'raw')));
        $data = "audio=" . $d;
        $res = $this->httpsRequest($url, $data, $xparam);
        file_put_contents(ROOT_PATH . 'public/uploads/response.log', $res);
        $res = (array)json_decode($res);

        if (!empty($res) && $res['code'] == 0) {

            return $this->apiResponse("success", "识别成功！", $res);

        } else {

            return $this->apiResponse("error", "识别失败！");

        }

    }

    /** 送一个科大讯飞的 httpRequest 封装数据请求方法
     * (讯飞接口我试了很多次 可能是我语音的问题，转码问题 若音频没问题 应该可以使用，自行测试哈,建议使用百度语音识别api,简单粗暴 just use SDK)
     * @param $url string link Api请求地址
     * @param null $data base64 语音文件
     * @param $xparam
     * @return mixed
     */
    public function httpsRequest($url, $data = null, $xparam)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $Appid = "***";//开放平台的appid
        $Appkey = "**********";//开放平台的Appkey
        $curtime = time();
        $CheckSum = md5($Appkey . $curtime . $xparam);
        $headers = array(
            'X-Appid:' . $Appid,
            'X-CurTime:' . $curtime,
            'X-CheckSum:' . $CheckSum,
            'X-Param:' . $xparam,
            'Content-Type:' . 'application/x-www-form-urlencoded; charset=utf-8'
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;

    }


    /** json数据返回方法封装
     * @param string $flag 转码状态
     * @param string $message 转码信息
     * @param array $data 转码成功的结果集
     * @return \think\response\Json
     */
    public function apiResponse($flag = 'error', $message = '', $data = array())
    {
        $result = array('flag' => $flag, 'message' => $message, 'data' => $data);
        return json($result);
    }
}