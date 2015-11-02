<?php
namespace Topxia\Component\Payment\Quickpay;

use Topxia\Component\Payment\Request;

class QuickpayAuthBankRequest extends Request 
{
	protected $url = 'Https://Pay.Heepay.com/API/ShortPay/ShortPayQueryAuthBanks.aspx';
	
	public function form()
    {        
    	$encrypt_data = $this->convertParams($this->params);
    	$sign = $this->signParams($this->params);
        $url = $this->url."?agent_id=".$this->options['key']."&encrypt_data=".$encrypt_data."&sign=".$sign;
        $result = $this->curlRequest($url);

        $xml = simplexml_load_string($result);
        $redir=(string)$xml->encrypt_data;
        $redirurl=$this->Decrypt($redir,$this->options['aes']);
        parse_str($redirurl,$authBanks);

        return $this->getListBank($authBanks);
    }

	public function signParams($params) {

        $signStr='';
        $signStr  = $signStr . 'agent_id=' . $this->options['key'];
        $signStr  = $signStr . '&key=' . $this->options['secret'];
        $signStr  = $signStr . '&timestamp=' . time()*1000;
        $signStr  = $signStr . '&user_identity=' .$this->options['account']."_".$params['userId'];
        $signStr  = $signStr . '&version=' . 1;
        $sign=md5(strtolower($signStr));

        return $sign;
    }

    protected function convertParams($params)
    {
        $converted = array();
        $converted['agent_id']=$this->options['key'];
        $converted['timestamp']=time()*1000;
        $converted['version']=1;
        $converted['user_identity']=$this->options['account']."_".$params['userId'];
        $encrypt_data = urlencode(base64_encode($this->Encrypt(http_build_query($converted),$this->options['aes'])));

        return $encrypt_data;

    }

	

    private function curlRequest($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function getListBank($authBanks)
    {

        $user_bank = array();
        if(array_key_exists('auth_uid_Info', $authBanks)){
            $authBanks['auth_uid_Info'] = trim($authBanks['auth_uid_Info'],';');
            $banks = explode(";",$authBanks['auth_uid_Info']);
            foreach ($banks as $key=>$bank) {
                $bankInfos = explode("_",$bank);
                $data = array();
                foreach ($bankInfos as $bankInfo) {
                    $data[]= $bankInfo;
                }
                list($user_bank[$key]['bankId'],$user_bank[$key]['bankName'],$user_bank[$key]['bankNumber'],$user_bank[$key]['type'],$user_bank[$key]['bankAuth'])=$data;
            }
        }

        return $user_bank;
    }

    private function Encrypt($data,$key){
        $decodeKey = base64_decode($key);
        $iv     = substr($decodeKey,0,16);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv);  
        return $encrypted;
    }

    private function Decrypt($data,$key){

        $decodeKey = base64_decode($key);
        $data = base64_decode($data);
        $iv = substr($decodeKey,0,16);
        $encrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $decodeKey, $data, MCRYPT_MODE_CBC, $iv); 

        return $encrypted;
    }
}