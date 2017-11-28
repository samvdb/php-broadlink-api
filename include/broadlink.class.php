<?
date_default_timezone_set("Asia/Taipei");



class Broadlink{




   	public function devtype(){
    	return sprintf("0x%x", $this->devtype);
   	}

    public function devmodel(){
        return self::getdevtype($this->devtype);
    }










}


class A1 extends Broadlink{

    function __construct($h = "", $m = "", $p = 80) {

         parent::__construct($h, $m, $p, 0x2714);

    }

    public function Check_sensors(){

        $data = array();

        $packet = self::bytearray(16);
        $packet[0] = 0x01;

        $response = $this->send_packet(0x6a, $packet);
        $err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
        

        if($err == 0){
            $enc_payload = array_slice($response, 0x38);

            if(count($enc_payload) > 0){

                $payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
                
                $data['temperature'] = ($payload[0x4] * 10 + $payload[0x5]) / 10.0;
                $data['humidity'] = ($payload[0x6] * 10 + $payload[0x7]) / 10.0;
                $data['light'] = $payload[0x8];
                $data['air_quality'] = $payload[0x0a];
                $data['noise'] = $payload[0x0c];

                switch ($data['light']) {
                    case 0:
                        $data['light_word'] = 'dark';
                        break;
                    case 1:
                        $data['light_word'] = 'dim';
                        break;                        
                    case 2:
                        $data['light_word'] = 'normal';
                        break;
                    case 3:
                        $data['light_word'] = 'bright';
                        break;
                    default:
                        $data['light_word'] = 'unknown';
                        break;
                }

                switch ($data['air_quality']) {
                    case 0:
                        $data['air_quality_word'] = 'excellent';
                        break;
                    case 1:
                        $data['air_quality_word'] = 'good';
                        break;                        
                    case 2:
                        $data['air_quality_word'] = 'normal';
                        break;
                    case 3:
                        $data['air_quality_word'] = 'bad';
                        break;
                    default:
                        $data['air_quality_word'] = 'unknown';
                        break;
                }

                switch ($data['noise']) {
                    case 0:
                        $data['noise_word'] = 'quiet';
                        break;
                    case 1:
                        $data['noise_word'] = 'normal';
                        break;                        
                    case 2:
                        $data['noise_word'] = 'noisy';
                        break;
                    default:
                        $data['noise_word'] = 'unknown';
                        break;
                }

            }

        }

        return $data;
        
    }   

}


class RM extends Broadlink{

	function __construct($h = "", $m = "", $p = 80, $d = 0x2712) {

    	 parent::__construct($h, $m, $p, $d);

    }

    public function Enter_learning(){

    	$packet = self::bytearray(16);
    	$packet[0] = 0x03;
    	$this->send_packet(0x6a, $packet);

	}

    public function Send_data($data){

    	$packet = self::bytearray(4);
    	$packet[0] = 0x02;

    	if(is_array($data)){
    		$packet = array_merge($packet, $data);
    	}
    	else{
    		for($i = 0 ; $i < strlen($data) ; $i+=2){
    			array_push($packet, hexdec(substr($data, $i, 2)));
    		}
    	}

    	$this->send_packet(0x6a, $packet);
    }	

	public function Check_data(){

		$code = array();

		$packet = self::bytearray(16);
  
    	$packet[0] = 0x04;
    	$response = $this->send_packet(0x6a, $packet);
    	$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
    	

    	if($err == 0){
	   		$enc_payload = array_slice($response, 0x38);

	   		if(count($enc_payload) > 0){

	    		$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
		    	
				$code = array_slice($payload, 0x04);
    		}
    	}

    	return $code;
	}

	public function Check_temperature(){

    	$temp = 0;

    	$packet = $this->bytearray(16);

	    $packet[0] = 0x01;
    	$response = $this->send_packet(0x6a, $packet);
    	$err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));

    	if($err == 0){
	   		$enc_payload = array_slice($response, 0x38);

	   		if(count($enc_payload) > 0){

	    		$payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
		    	
				$temp = ($payload[0x4] * 10 + $payload[0x5]) / 10.0;

    		}
    	}
      
      	return $temp;

    }

}

class MP1 extends Broadlink{

    function __construct($h = "", $m = "", $p = 80, $d = 0x4EB5) {

         parent::__construct($h, $m, $p, $d);

    }

    public function Set_Power_Mask($sid_mask, $state){

        $packet = self::bytearray(16);
        $packet[0x00] = 0x0d;
        $packet[0x02] = 0xa5;
        $packet[0x03] = 0xa5;
        $packet[0x04] = 0x5a;
        $packet[0x05] = 0x5a;
        $packet[0x06] = 0xb2 + ($state ? ($sid_mask<<1) : $sid_mask);
        $packet[0x07] = 0xc0;
        $packet[0x08] = 0x02;
        $packet[0x0a] = 0x03;
        $packet[0x0d] = $sid_mask;
        $packet[0x0e] = $state ? $sid_mask : 0;

        $this->send_packet(0x6a, $packet);
    }

    public function Set_Power($sid, $state){

        $sid_mask = 0x01 << ($sid - 1);

        $this->Set_Power_Mask($sid_mask, $state);
    }

    public function Check_Power_Raw(){

        $packet = self::bytearray(16);
        $packet[0x00] = 0x0a;
        $packet[0x02] = 0xa5;
        $packet[0x03] = 0xa5;
        $packet[0x04] = 0x5a;
        $packet[0x05] = 0x5a;
        $packet[0x06] = 0xae;
        $packet[0x07] = 0xc0;
        $packet[0x08] = 0x01;

        $response = $this->send_packet(0x6a, $packet);
        $err = hexdec(sprintf("%x%x", $response[0x23], $response[0x22]));
        

        if($err == 0){
            $enc_payload = array_slice($response, 0x38);

            if(count($enc_payload) > 0){

                $payload = $this->byte2array(aes128_cbc_decrypt($this->key(), $this->byte($enc_payload), $this->iv()));
                return $payload[0x0e];    
            }

        }

        return false;

        
    }

    public function Check_Power(){

        $data = array();

        if($state = $this->Check_Power_Raw()){

            $data[0] = bool($state & 0x01);
            $data[1] = bool($state & 0x02);
            $data[2] = bool($state & 0x04);
            $data[3] = bool($state & 0x08);

        }

        return $data;

    }  

}

?>
