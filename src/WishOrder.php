<?php
namespace service\wish;

use service\wish\WishApi;
use think\Loader;
use think\Cache;

/**
*  wish 订单管理
*/
class WishOrder
{	
	/**
	 * @var null
	 */
	public static $instance = NULL;

	/**
	 * 实例化数据
	 * @return [type] [description]
	 */
	public static function instance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new WishOrder;
		}
		return self::$instance;
	}

	/**
	 * 获取订单
	 * @return [type] [description]
	 */
	public function getOrder($data = [])
	{
		$url = "https://merchant.wish.com/api/v2/order/create-download-job?access_token=".WishApi::instance()->access_token;
		if(!empty($data))
		{
			foreach ($data as $key => $value) 
			{
				$url .= "&".$key."=".$value;
			}
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			$job_id = $response['data']['job_id'];
			
			//加入消息队列中
			#code .....
			Cache::store('redis')->lpush('getorder',$job_id);
			return true;
		}
	}

	/**
	 * 检索订单
	 * @return [type] [description]
	 */
	public function retrieveOrder(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/order?access_token='.WishApi::instance()->access_token;
		//拼接
		foreach ($data as $key => $value) 
		{
			$url = '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 获取订单下载的状态，用来下载文件
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function downloadOrder(array $data)
	{
		$job_id = $data['job_id'];  //下载启动的标识
		$url = "https://merchant.wish.com/api/v2/order/get-download-job-status?access_token=".WishApi::instance()->access_token."&job_id=".urlencode($job_id);
		$goods = WishApi::instance()->getdata($url);
		if($goods['code'] ==0 && $goods['data']['status'] == 'FINISHED')
		{
			$filename = $job_id.".csv";
			if(WishApi::instance()->curl($goods['data']['download_link'],'./wish/',$filename))
			{
				//文件下载完开始存入数据库中
				if($this->saveGoods($job_id))
				{
					echo 'ok';
					//把队列中的job_id删除掉
				}
				//return ['state' => true, 'data' => $job_id];
			}
		}
		return ['state' => false,'data' => $goods['data']['status']];
	}
	
	/**
	 * 订单标识
	 * @param  [type] $job_id [description]
	 * @return [type]         [description]
	 */
	public function saveOrders($job_id)
	{
		$file = @fopen('./wish/'.$job_id.'.csv','r');
		while($data = fgetcsv($file)) 
		{
			$order_list[] = $data;
		}
		if(!empty($order_list))
		{
			unset($order_list[0]);
		}

		$order_list = array_reverse($order_list);
		$data = [];
		//入库
		foreach ($order_list as $key => $value) 
		{
			$data[$key]['transaction_date'] = $value[0];
			$data[$key]['order_id'] = $value[1];
			$data[$key]['transaction_id'] = $value[2];
			$data[$key]['order_state'] = trun($value[3]);
			$data[$key]['sku'] = $value[4];
			$data[$key]['product'] = $value[5];
			$data[$key]['product_id'] = $value[6];
			$data[$key]['product_link'] = $value[7];
			$data[$key]['variation'] = $value[8];
			$data[$key]['price'] = ltrim($value[9],'$');
			$data[$key]['cost'] = ltrim($value[10],'$');
			$data[$key]['shipping'] = ltrim($value[11],'$');
			$data[$key]['shipping_cost'] = ltrim($value[12],'$');
			$data[$key]['quantity'] = $value[13];
			$data[$key]['total_cost'] = ltrim($value[14],'$');
			$data[$key]['shipped_on'] = $value[15];
			$data[$key]['confirmed_delivery'] = $value[16];
			$data[$key]['provider'] = $value[17];
			$data[$key]['tracking'] = $value[18];
			$data[$key]['tracking_confirmed'] = $value[19];
			$data[$key]['tracking_confirmed_date'] = strtotime($value[20]);
			$data[$key]['shipping_address'] = $value[21];
			$data[$key]['name'] = $value[22];
			$data[$key]['first_name'] = $value[23];
			$data[$key]['last_name'] = $value[24];
			$data[$key]['street_address_1'] = $value[25];
			$data[$key]['street_address_2'] = $value[26];
			$data[$key]['city'] = $value[27];
			$data[$key]['state'] = $value[28];
			$data[$key]['zipcode'] = $value[29];
			$data[$key]['country'] = $value[30];
			$data[$key]['last_updated'] = strtotime($value[31]);
			$data[$key]['phone_number'] = $value[32];
			$data[$key]['country_code'] = $value[33];
			$data[$key]['refund_responsibility'] = $value[34];
			$data[$key]['refund_responsibility_amount'] = $value[35];
			$data[$key]['refund_date'] = strtotime($value[36]);
			$data[$key]['refund_reason'] = $value[37];
			$data[$key]['is_wish_express'] = $value[38];
			$data[$key]['wish_express_delivery_deadline'] = $value[39];
		}
		//入库	
		Loader::model('WishPlatformOnlineOrder')->addAll($data);
		return true;
	}
}