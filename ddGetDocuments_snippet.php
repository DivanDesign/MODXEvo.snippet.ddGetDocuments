<?php
/**
 * ddGetDocuments
 * @version 0.3.2 (2015-10-11)
 * 
 * A snippet for fetching and parsing resources from the document tree by a custom rule.
 * 
 * @param string $provider - Name of the provider that will be used to fetch documents.
 * @param string $providerParams - Parameters to be passed to the provider. The parameter must be set as a query string,
 * e.g. $providerParams = 'parentId=1&depth=2'.
 * 
 * @param integer $total - The maximum number of the resources that will be returned.
 * @param string $filter - The filter condition in SQL-style to be applied while resource fetching. Default: '`published` = 1';
 * Notice that all fields/tvs names specified in the filter parameter must be wrapped in back quotes (`).
 * @param string $filterFieldDelimiter - The field delimiter to be used in the passed filter query. Default: '`';
 * @param integer $offset - Resources offset.
 * @param string $orderBy - A string representing the sorting rule. Default: '`id` ASC'.
 * 
 * @param 'string'|'raw' $format - Format of the output. Default: 'string'.
 * @param string $formatParams - Parameters to be passed to the specified formatter. The parameter must be set as a query string,
 * e.g. $formatParams = 'itemTpl=chunk_1&wrapperTpl=chunk_2'.
 * 
 **/

//TODO: Extenders support with parameters like $extenders = 'pagination', $extendersParams = 'pagination[paginationWrapperTpl]=wrapperChunk&pagination[paginationPageTpl]=pageChunk...'
//TODO: parameters for the pagination extender $wrapperTpl, $pageTpl, $nextTpl, $nextInactiveTpl, $previousTpl, $previousInactiveTpl, $currentPageTpl

global $modx;
$output = false;

if(is_file($modx->config['base_path'].'vendor/autoload.php')){
	require_once($modx->config['base_path'].'vendor/autoload.php');
}

if(!class_exists('\ddTools')){
	require_once($modx->config['base_path'].'assets/snippets/ddTools/modx.ddtools.class.php');
}

if(!class_exists('\ddGetDocuments\DataProvider\DataProvider')){
	require_once($modx->config['base_path'].'assets/snippets/ddGetDocuments/require.php');
}

$provider = isset($provider)? $provider: 'parent';
$providerClass = \ddGetDocuments\DataProvider\DataProvider::includeProviderByName($provider);
$providerParams = isset($providerParams)? $providerParams: '';

$total = isset($total)? $total: null;
$offset = isset($offset)? $offset: 0;
$orderBy = isset($orderBy)? $orderBy: '`id` ASC';
$filter = isset($filter)? $filter: null;
$filterFieldDelimiter = isset($filterFieldDelimiter)? $filterFieldDelimiter: '`';

$format = isset($format)? $format: 'string';
$formatParams = isset($formatParams)? $formatParams: '';

$extenders = isset($extenders)? $extenders: '';
$extendersParams = isset($extendersParams)? $extendersParams: '';

if(class_exists($providerClass)){
	$dataProvider = new $providerClass;
	parse_str($providerParams, $providerParamsArray);
	
	$extendersNamesArray = explode(',', $extenders);
	parse_str($extendersParams, $extendersParamsArray);
	
	if(!empty($extendersNamesArray) && !empty($extendersParamsArray)){
		//If we have a single extender then make sure that extender params set as an array
		//like [extenderName => [extenderParameter_1, extenderParameter_2, ...]]
		if(count($extendersNamesArray) === 1){
			if(!isset($extendersParamsArray[$extendersNamesArray[0]])){
				$extendersParamsArray = array(
					$extendersNamesArray[0] => $extendersParamsArray
				);
			}
		}else{
			//Make sure that for each extender there is an item in $extendersParamsArray 
			foreach($extendersNamesArray as $extenderName){
				if(!isset($extendersParamsArray[$extenderName])){
					$extendersParamsArray[$extenderName] = array();
				}
			}
		}
	}
	
	parse_str($formatParams, $formatParamsArray);
	
	$input = new \ddGetDocuments\Input(
		array(
			'offset' => $offset,
			'total' => $total,
			'orderBy' => $orderBy,
			'filter' => $filter,
			'filterFieldDelimiter' => $filterFieldDelimiter
		),
		$providerParamsArray,
		$extendersParamsArray,
		$formatParamsArray
	);
	
	//Extenders storage
	$extenders = array();
	
	foreach($extendersNamesArray as $extenderName){
		$extenderClass = \ddGetDocuments\Extender\Extender::includeExtenderByName($extenderName);
		$extender = new $extenderClass;
		$extenders[$extenderName] = $extender;
		
		$input = $extender->applyToInput($input);
	}
	
	$data = new \ddGetDocuments\Output($dataProvider->get($input));
	
	foreach($extenders as $extenderName => $extender){
		$data->extenders[$extenderName] = $extender->applyToProvider($data);
	}
	
	switch($format){
		default:
			$parserClass = \ddGetDocuments\Format\Format::includeFormatByName($format);
			$parser = new $parserClass;
			
			$output = $parser->parse($data, $formatParamsArray);
			
			break;
		case 'raw':
			$output = $data;
			break;
	}
}

return $output;
?>