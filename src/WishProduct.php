<?php
namespace service\wish;

use service\wish\WishApi;
use think\Loader;
use think\Db;

/**
* wish 产品管理
*/
class WishProduct
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
			self::$instance = new WishProduct;
		}
		return self::$instance;
	}
	
	/**
	 * 创建产品
	 * @return [type] [description]
	 */
	public function createProduct(array $data)
	{
		$url = "https://merchant.wish.com/api/v2/product/add?access_token=".WishApi::instance()->access_token."&format=json";
		foreach ($data as $key => $value) 
		{
			$url .= "&".$key."=".$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];
			//程序还要更改数据库里的product_id
		}

		return ['state' => false,'message' => $response['message']];
	}

	/**
	 * 更新产品
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function updateProduct(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/product/update?access_token='.WishApi::instance()->access_token;
		//拼接数据
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);

		if($response['code'] == 0)
		{
			echo 'Update product success';
			exit;
		}
		echo 'Update product failure';
	}

	/**
	 * 产品的变体
	 * @return [type] [description]
	 */
	public function variantProduct(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant/add?access_token='.WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 检索变体产品的变化
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function retrieveProduct(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant?access_token='.WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);

		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 更新产品的变体
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function updateVariation(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant/update?access_token='.WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 更换变体的sku
	 * @param  array  $data  sku,new_sku
	 * @return [type]       [description]
	 */
	public function changeSku(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant/change-sku?access_token='.WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] == 0)
		{
			return ['state' => true,'data' => $response['data']];	
		}
		return ['state' => false];
	}

	/**
	 * 获取商品
	 * @return [type] [description]
	 */
	public function getGoods($data = [])
	{
		$url = 'https://merchant.wish.com/api/v2/product/create-download-job?access_token='.WishApi::instance()->access_token;
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

			return $job_id;
			//入队列
			# code ...
		}
	}

	/**
	 * 启用产品/上架
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function enableProduct(array $data)
	{
		$url = "https://merchant.wish.com/api/v2/product/enable?access_token=".WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= "&".$key."=".$value;
		}
		$response = WishApi::instance()->getdata($url);

		if($response['code'] == 0)
		{
			echo 'Enable product success';
			exit;
		}
		echo 'Enable product failure';
	}

	/**
	 * 禁用产品/下架
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function disableProduct(array $data)
	{
		$url = "https://merchant.wish.com/api/v2/product/disable?access_token=".WishApi::instance()->access_token;
		//拼接参数
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}

		$response = WishApi::instance()->getdata($url);

		if($response['code'] == 0)
		{
			echo 'Disable product success';
			exit;
		}
		echo 'Disable product failure';
	}

	/**
	 * 启用变体
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function enableVariation(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant/enable?access_token='.WishApi::instance()->access_token;
		//拼接
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}

		$response = WishApi::instance()->getdata($url);
		if($response['code'] ==0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 禁用变体
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function disableVariation(array $data)
	{
		$url = 'https://merchant.wish.com/api/v2/variant/disable?access_token='.WishApi::instance()->access_token;
		//拼接
		foreach ($data as $key => $value) 
		{
			$url .= '&'.$key.'='.$value;
		}
		$response = WishApi::instance()->getdata($url);
		if($response['code'] ==0)
		{
			return ['state' => true,'data' => $response['data']];
		}
		return ['state' => false];
	}

	/**
	 * 更改库存
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function udpateInventory(array $data)
	{
	    $url = 'https://merchant.wish.com/api/v2/variant/update-inventory?access_token='.WishApi::instance()->access_token;
	    foreach ($data as $key => $value) 
	    {
	    	$url .= '&'.$key.'='.$value;
	    }
	    $response = WishApi::instance()->getdata($url);
	    if($response['code'] == 0)
	    {
	    	return ['state' => true,'data' => $response['data']];
	    }
	    return ['state' => false];
	}

	/**
	 * 获取产品下载的状态，用来下载文件
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function downloadProduct(array $data)
	{
		$job_id = $data['job_id'];  //下载启动的标识
		$url = "https://merchant.wish.com/api/v2/product/get-download-job-status?access_token=".WishApi::instance()->access_token."&job_id=".urlencode($job_id);
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
	 * 商品入库
	 * @param  [type] $job_id [description]
	 * @return [type]         [description]
	 */
	public function saveGoods($job_id)
	{
		$file = @fopen('./wish/'.$job_id.'.csv','r');
		while($data = fgetcsv($file))
		{
			$goods_list[] = $data;
		}
		if(!empty($goods_list))
		{
			unset($goods_list[0]);
		}
		// echo "<pre>";
		// var_dump($goods_list);die;
		$data = [];
		$variant = [];
		$j = 0;
		$i = 0;
		$product_id = 0;
		foreach($goods_list as $key => $value) 
		{
			if(!empty($value[0]))
			{
				$data[$i]['product_id'] = $value[0];
				$data[$i]['name'] = $value[1];
				$data[$i]['description'] = $value[2];
				$data[$i]['of_wishes'] = $value[3];
				$data[$i]['of_sales'] = $value[4];
				$data[$i]['variation_id'] = $value[5];
				$data[$i]['sku'] = $value[6];
				$data[$i]['cost'] = ltrim($value[7],'$');
				$data[$i]['price'] = ltrim($value[8],'$');
				$data[$i]['shipping'] = ltrim($value[9],'$');
				$data[$i]['inventory'] = $value[10];
				$data[$i]['status'] = turn($value[11]);
				$data[$i]['is_promoted'] =turn($value[12]);
				$data[$i]['review_state'] = turn($value[13]);
				$data[$i]['counterfeit_reasons'] = $value[14];
				$data[$i]['image_url'] = $value[15];
				$data[$i]['brand'] = $value[16];
				$data[$i]['modified'] = strtotime($value[17]);
				$data[$i]['created'] = strtotime($value[18]);
				$data[$i]['warning_id'] = $value[19];
				$data[$i]['wish_express_countries'] = $value[20];
				$product_id = $data[$i]['product_id'];
				$i++;
			}else
			{
				$variant[$j]['product_id'] = $product_id;
				$variant[$j]['variation_id'] = $value[5];
				$variant[$j]['sku'] = $value[6];
				$variant[$j]['cost'] = ltrim($value[7],'$');
				$variant[$j]['price'] = ltrim($value[8],'$');
				$variant[$j]['shipping'] = ltrim($value[9],'$');
				$variant[$j]['inventory'] = $value[10];
				$variant[$j]['status'] = turn($value[11]);
				$j++;
			}
		}

		var_dump(count($variant));
		echo '---';
		var_dump(count($data));die;

		// 启动事务
		Db::startTrans();
		try
		{
			//入库
			Loader::model('WishPlatformOnlineGoods')->addAll($data);
			//另外一个库
			Loader::model('WishPlatformOnlineGoodsVariation')->addAll($variant);
			Db::commit();
		}catch(\Exception $e)
		{
			Db::rollback();
			var_dump($e->getMessage());
		}
		
		return true;
	}
}