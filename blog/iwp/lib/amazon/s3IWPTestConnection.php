<?php
	
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

function repositoryAmazons3($args){
	require_once(APP_ROOT . '/lib/amazon/autoload.php');
	
	extract($args);
	
	try{
		$s3 = S3Client::factory(array(
			'key' => trim($as3_access_key),
			'secret' => trim(str_replace(' ', '+', $as3_secure_key)),
			'region' => $as3_bucket_region,
			'signature' => 'v4',
			'ssl.certificate_authority' => false
		));
		$objects = $s3->getIterator('ListObjects', array(
			'Bucket' => $as3_bucket,
		));
		foreach ($objects as $object){
			echo $s3->getObjectUrl($as3_bucket,$object['Key']);
			break; 
		}
		return array('status' => 'success');
	}
	catch (Exception $e){
         return array('status' => 'error', 'errorMsg' => $e->getMessage());
	}
}