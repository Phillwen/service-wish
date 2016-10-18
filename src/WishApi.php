<?php
namespace service\wish;

/**
* wish 平台api
*/
class WishApi
{
	/**
	 * wish 授权账号信息
	 * @var null
	 */
	public static $instance = NULL;
	public $access_token = 'bd0150b56b294288ad8d8bcc6870f456';
	public $redirect_uri = 'https://107.191.48.158';
	public $client_id = '57fb1d3419034319422579e6';
	public $client_secret = 'a940b99dd32e4be6a0db83027d043770';
	public $code = 'eb585ffd403444a991963a8f5d258d13';

	/**
	 * 实例化数据
	 * @return [type] [description]
	 */
	public static function instance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new WishApi;
		}
		return self::$instance;
	}


	/**
	 * 构造函数
	 */
	public function __construct()
	{
		$this->checkToken();
	}	

	/**
	 * 获取token
	 * @return [type] [description]
	 */
	public function getToken()
	{
		//公共
		$url = sprintf(
		    "https://merchant.wish.com/api/v2/oauth/access_token?&client_id=%s&client_secret=%s&code=%s&redirect_uri=%s&grant_type=authorization_code", $this->client_id, $this->client_secret, $this->code, $this->redirect_uri);

		$context = $this->context();

		// Send the request
		$response = file_get_contents($url, TRUE, $context);
		
		$access_token = json_decode($response,true);

		if($access_token['code'] == 0 && $access_token['code'] == 200)
		{
			//保存到本地
			@file_put_contents('./wish/access_token.txt',serialize($access_token['data']));

			//赋值给当前的access_token
			$this->access_token = $access_token['data']['access_token'];  //赋值给当前的access_token

			return ['state' => true,'access_token' => $access_token['data']['access_token']];
		}

		return ['state' => false,'message' => $access_token['message']];
	}

	/**
	 * 更新token
	 * @return [type] [description]
	 */
	public function refreshToken($access_token)
	{
		$refresh_token = urlencode($access_token);
		$url = sprintf(
    	"https://merchant.wish.com/api/v2/oauth/refresh_token?&client_id=%s&client_secret=%s&refresh_token=%s&grant_type=refresh_token",
    	urlencode($this->client_id), urlencode($this->client_secret),$refresh_token);
		$context = $this->context();
		//send
		$response = file_get_contents($url,true,$context);
		$response = json_decode($response,true);
		if($response['code'] == 0)
		{
			$this->access_token = $response['data']['access_token'];
			return $response['data'];
		}
	}

	/**
	 * 检查token是否已过期
	 * @return [type] [description]
	 */
	private function checkToken()
	{
		$fp = @fopen('./wish/access_token.txt','r');
		if($fp)
		{
			$arr = unserialize(fgets($fp));  //获取一行
			if($arr['expiry_time'] < time())
			{
				//表示已过期
				$result = $this->refreshToken($arr['access_token']);
				@fwrite($fp,serialize($result));
				fclose($fp);
				exit;
			}else
			{
				$this->access_token = $arr['access_token'];
			}
		}
	}

	/**
	 * 请求头部信息
	 * @return [type] [description]
	 */
	private function context()
	{
		$context = stream_context_create(array(
		    'http' => array(
		        'method'        => 'POST',
		        'ignore_errors' => true,
		    ),
		));

		return $context;
	}

	/**
	 * 获取数据
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public function getdata($url)
	{
		$result = file_get_contents($url,true,$this->context());
		return json_decode($result,true);
	}

	/**
	 * 公用的请求方法
	 * @return [type] [description]
	 */
	public function curl($url,$dir,$filename)
	{
		if(!empty($dir) && !is_dir($dir))
		{
			@mkdir($dir,0777,true);
		}
		$curl = curl_init (); // 启动一个CURL会话
		curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
		curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 3000 ); // 设置超时限制防止死循环
		//curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header ); // 设置HTTP头
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, 'GET' );
		$result = curl_exec ( $curl ); // 执行操作
		@file_put_contents($dir.$filename,$result);
		curl_close ( $curl ); // 关闭CURL会话
		return true;
	}
}