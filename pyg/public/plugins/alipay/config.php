<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016101600697028",

		//商户私钥
		'merchant_private_key' => "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCVuMk3r3cclliLaosJ49fPWXIyyZclc5E4+3lI6DRZ8PAlfnejQdMHFGaUkkYvoW10cJWYx7qB/JJgFCMfUZNaaelSNoV99hZ7MYN3fxm6wdVQA4sLvcMtJ0d4dzuZJZEuqn0HuUOxoP5REG2iDXTJXvglTFHpyet9KAm2kj95EQbp2dSCP3MeEsXk7y+x5Q1P4tF/ljTocbeGr7143bIlUBB+dO107Ptpf0kCURFIqgvJK2zaWyFCWKRVLMZEh7id4leuJyi8isk1YkvZBmbmc/ml1YklhmRqxc5vy9WTBIKbvfMNkhRpofI7Inl738DONGSRHd4tF8IrIz9y/5ZXAgMBAAECggEAeDb3sXN8kwKQs9hnLRi5Ni6eh/LfHl4Nk5AvEQAI0NKpL6G80+PNWhjiSJauLh5ScTTYmOR9d1NiAC0LCmGIjAcQJUfLpZjK8j2OTotKEG8EJIvDwDislvu74hjyTIQibzLK00HI/b3DlTk4ne5qACn12pTODIZpQ4O7UNFaj4yDZNwWkUYVe47pIuQ93oqvmGbZlJ3gxVJ0Pc0TXgdcMJUdxIvQHVD3F7VzO6KzHmWfKiBXF4f3Zu7Ile7Wk9JtxqYJ3npykSqHRW1KQQ1utVAoVy+sEJnSe8ng64HS3UPfNFwCOmsJpuf53r6a+ZkcWFHMNUjQN7zwVkmuhAeyUQKBgQDjoecFKqz575yzGUyQHfdSa9g1hUU3/7EZ7/DBrOcr8eKAdmTxF9bWhy8BrekHZvj2Rsdafa+ndirmus76kb0ZWvDpGkcR2nLoV77PToMI8nVmX7a8/j1o0GFju78ECscwIrQOfde6ov5BAYuq/Ahnv/23PPCLW+l+El6KBuX1zwKBgQCoYVJlGpJoDHy3MZk19dGrDgsigbsU28bDHbVtjdLuVMaiQKJrHTz28NZ4N6tXhrQMhmMwqqP6qp1CDGx5yJdn1F4vnhDhSZ/Y9xvhs6XnluTdlbOYsfx00N+VujDgOpYHIzMvE4OXFG1ns7AgFsRZtJDhg9lOyUY9gOwwXdSA+QKBgEEA6/Lqw+i/xzTR5a5GEGmGCIEIJMMgDlgEz/DOylkfQvpCVQMQNraedgr5udD2U+QoRCeQOsgMk6W99PtTAPgrox499cugYS3+WKvklMDKVEI+1PVVeP3ke9s6MwsZdNZHIrn8r81JXGf94/+Y52GRTwezrFFvltb8seiinaq7AoGBAJPufR9KixpS7jdcogigYYbxHaNBawXWWPgPX9hSY+D0Jldihc+prmgeC68u6aBKPmFKqFaXdN3Di8n9dEhCjZKxJ9aZ/qaPsppB8AMJfFbNYWG0JkefB/fxkp3PKSW3ExxONqfDczcGwV/8pF5s4jEVzLv0xLTNbKKYY+nSZeKxAoGBALyR2E7a5m+cbjijVQ+SQc+7QhHIY/vRTZ6VUdiBo5THjY6VNYADF6HrvcT06MOIqG3GAEeSwvQIiJc7uNT+aFwiSITfjcy63DHBEaU2gA9p54s+VYADGFDX6BQXQNTS7UFvtjNeeHy0+2k1yiTkRtvEK3zjRqYTehANh640nz9o",
		
		//异步通知地址  必须是外网地址  本地地址无法访问成功
		'notify_url' => "http://www.pyg.com/home/order/notify",
		
		//同步跳转  本地地址也可以测试成功
		'return_url' => "http://www.pyg.com/home/order/callback",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		//正式环境网关
		// 'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
		//沙箱环境网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjyX9PLT7ibjXkYdCisVuuzfY49WE81lmFMDt52h1j8F/8Qd1sKR1Ftb1zuJJk+D0TDwFi35xxeY6LQ1LPhpttt1U8Rfu61tnqxhGmenEBMNLkQRLrR1MXHQhM7WG1zEnLvL23YO2oSG/yrSEP9EcP5boMQGybFmXNxaxhjNha+uUtJ5FqBZyV3XYunvbSr15TZF7NelEfdTqdYgqlJ2lvfwQlBtxyhYsaEg0skXP8DW2YNuBbrIZyXuwGBe5xQ9yi2q9wFomQbBjNWT7Lg0bVvjKVRxdyPDI6vyQVHmlENKWZGPz8I5TXHt8GBSdYdACxvYAQQD2fLzOiobIA5QZ4QIDAQAB",
);