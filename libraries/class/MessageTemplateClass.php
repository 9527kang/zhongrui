<?php

use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\User\Model\MessageReceiveModel;
/**
 * Created by PhpStorm.
 * User: quanke
 * Date: 2018/01/02
 * Time: 09:51
 */
class MessageTemplateClass {

    static public function templateCode()
    {
        $codeArr = [
            'password_back' => '密码找回',
            'task_publish_success' => '任务发布成功',
            'task_win' => '任务中标',
            'task_audit_failure' => '任务审核失败',
            'audit_success' => '审核通过',
            'task_delivery' => '任务交稿',
            'trading_rights' => '交易维权受理',
            'trading_rights_result' => '交易维权结果',
            'agreement_documents' => '任务交付',
            'Automatic_choose' => '自动选稿',
            'manuscript_settlement' => '稿件结算',
            'task_failed' => '任务失败',
            'task_finish' => '任务完成',
            'report' => '举报通知',
            'feedback' => '意见反馈',
            'registration_activation' => '注册激活',
            'shop_rights' => '店铺维权结果',
            'question_accept' => '问答采纳',
            'bid_work_check_success' => '招标任务阶段审核稿件通过',
            'bid_work_check_failure' => '招标任务阶段审核稿件失败',
            'report_result' => '举报处理结果'
        ];
        return $codeArr;
    }

    /**
     * 根据短信模板代号发送站内信息
     * @param string $code 模板代号
     * @param int $uid 接收人uid
     * @param int $type 站内信类型 1:系统消息 2:交易消息
     * @param array $arr 信息变量
     * @param string $messageTitle 信息标题
     * @return bool|static
     */
    static public function getMeaasgeByCode($code,$uid,$type=1,$arr,$messageTitle='')
    {
        //获取信息内容
        $message = self::getMessageContent($code,$arr,1);

        if(isset($message) && !empty($message)){
            if(empty($messageTitle)){
                $codeArr = self::templateCode();
                if(in_array($code,array_keys($codeArr))){
                    $messageTitle = $codeArr[$code];
                }
            }
            $data = [
                'message_title'   => $messageTitle,
                'code'            => $code,
                'message_content' => $message,
                'js_id'           => $uid,
                'message_type'    => $type,
                'receive_time'    => date('Y-m-d H:i:s',time()),
                'status'          => 0,
            ];
            $res = MessageReceiveModel::create($data);
            return $res;
        }
        return false;

    }


    /**
     * 根据短信模板代号发送邮件
     * @param string $code 模板代号
     * @param string $email 接收人email
     * @param array $arr 信息变量
     * @param string $messageTitle 信息标题
     * @return bool|static
     */
    static public function sendEmailByCode($code,$email,$arr,$messageTitle='')
    {
        if($email){
            //获取信息内容
            $message = self::getMessageContent($code,$arr,2);
            if(isset($message) && !empty($message)){
                if(empty($messageTitle)){
                    $codeArr = self::templateCode();
                    if(in_array($code,array_keys($codeArr))){
                        $messageTitle = $codeArr[$code];
                    }
                }
                $data = [
                    'title' => $messageTitle,
                    'email' => $email,
                    'message' => $message
                ];
                if (\MessagesClass::sendMsg($data, 'email.sitemessage')){
                    return true;
                }
                return false;
            }
            return false;

        }
        return false;
    }

    /**
     * 获取信息内容
     * @param  string $code  模板别名
     * @param array $arr 变量数组
     * @param int $type 1: 站内信 2:邮件
     * @return mixed
     */
    static public function getMessageContent($code,$arr=[],$type=1)
    {
        $messageVariableArr = [];
        if(!empty($arr) && is_array($arr)){
            foreach($arr as $k => $v){
                $messageVariableArr[$k] = $v;
            }
        }
        $message = MessageTemplateModel::sendMessage($code,$messageVariableArr,$type);
        return $message;
    }



}